<?php

namespace App\Classes;

use Illuminate\Support\Facades\DB;

/**
 * Do khoang cach giua hai diem hanh chinh / du an.
 *
 * Dung cho luat o muc 3 cua file noxh_image/cong-thuc.jpg: dang co nha o van
 * duoc mua neu nha cach noi lam viec >= 30km VA noi lam viec cach du an
 * <= 30km.
 *
 * TOA DO LAY O DAU: uu tien toa do cua chinh phuong/xa (vn_wards.lat / lng,
 * nap bang `php artisan noxh:toa-do --xa`); phuong/xa nao chua nap thi tam
 * lay toa do tinh/thanh. Hai diem cung mot tinh ma ca hai deu phai lui ve
 * toa do tinh thi khoang cach ra 0 - ket qua NGHIENG VE PHIA CHAT (khong cho
 * qua luat "cach noi lam viec >= 30km"), dung huong an toan hon la cho qua
 * nham. Nap them toa do phuong/xa la tinh chinh xac ngay, khong phai sua
 * dong ma nao.
 */
class NoxhKhoangCach
{
    /** Ban kinh trai dat, km. */
    private const BAN_KINH = 6371.0;

    /** Bo nho tam trong mot luot chay - mot luot cham diem hoi vai lan. */
    private static array $nho = [];

    /**
     * Toa do cua mot phuong/xa.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function toaDoXa(?string $maXa): ?array
    {
        $maXa = trim((string) $maXa);

        if ($maXa === '') {
            return null;
        }

        if (array_key_exists('x' . $maXa, self::$nho)) {
            return self::$nho['x' . $maXa];
        }

        $xa = DB::table('vn_wards')->where('code', $maXa)->first(['lat', 'lng', 'province_code']);

        $ra = null;

        if ($xa) {
            $ra = self::hopLe($xa->lat, $xa->lng) ?: self::toaDoTinh($xa->province_code);
        }

        return self::$nho['x' . $maXa] = $ra;
    }

    /**
     * Toa do cua mot tinh/thanh pho.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function toaDoTinh(?string $maTinh): ?array
    {
        $maTinh = trim((string) $maTinh);

        if ($maTinh === '') {
            return null;
        }

        if (array_key_exists('t' . $maTinh, self::$nho)) {
            return self::$nho['t' . $maTinh];
        }

        $tinh = DB::table('vn_provinces')->where('code', $maTinh)->first(['lat', 'lng']);

        return self::$nho['t' . $maTinh] = $tinh ? self::hopLe($tinh->lat, $tinh->lng) : null;
    }

    /**
     * Toa do cua mot dia chi trong bo kiem tra: uu tien phuong/xa, thieu thi
     * lui ve tinh.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function toaDo(?string $maTinh, ?string $maXa): ?array
    {
        return self::toaDoXa($maXa) ?: self::toaDoTinh($maTinh);
    }

    /**
     * Khoang cach duong chim bay giua hai diem, don vi km.
     *
     * Thieu mot trong hai diem thi tra null - nguoi goi tu quyet dinh coi la
     * "chua ro" chu khong duoc coi la 0.
     */
    public static function km(?array $a, ?array $b): ?float
    {
        if (!$a || !$b) {
            return null;
        }

        [$lat1, $lng1] = $a;
        [$lat2, $lng2] = $b;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $h = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round(self::BAN_KINH * 2 * asin(min(1.0, sqrt($h))), 2);
    }

    /** Toa do cua mot du an (bang products). */
    public static function toaDoDuAn(int $id): ?array
    {
        if (array_key_exists('d' . $id, self::$nho)) {
            return self::$nho['d' . $id];
        }

        $d = DB::table('products')->where('id', $id)
            ->first(['latitude', 'longitude', 'province_code', 'ward_code']);

        $ra = null;

        if ($d) {
            $ra = self::hopLe($d->latitude, $d->longitude) ?: self::toaDo($d->province_code, $d->ward_code);
        }

        return self::$nho['d' . $id] = $ra;
    }

    /**
     * Khoang cach ngan nhat tu mot diem toi mot trong cac du an.
     *
     * @param  array<int,int>  $duAn
     */
    public static function ganNhat(?array $diem, array $duAn): ?float
    {
        if (!$diem || !$duAn) {
            return null;
        }

        $gan = null;

        foreach ($duAn as $id) {
            $km = self::km($diem, self::toaDoDuAn((int) $id));

            if ($km !== null && ($gan === null || $km < $gan)) {
                $gan = $km;
            }
        }

        return $gan;
    }

    /** Xoa bo nho tam - chi dung trong bai kiem tra. */
    public static function quen(): void
    {
        self::$nho = [];
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private static function hopLe($lat, $lng): ?array
    {
        if ($lat === null || $lng === null || $lat === '' || $lng === '') {
            return null;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        // Toa do 0,0 la giua Dai Tay Duong - do la o trong bi nhap thanh so
        // chu khong phai mot dia diem that.
        if (abs($lat) < 0.0001 && abs($lng) < 0.0001) {
            return null;
        }

        return [$lat, $lng];
    }
}
