<?php

namespace App\Console\Commands;

use App\Classes\NoxhKhoangCach;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Nap toa do that cho du an va phuong/xa.
 *
 * Ban do du an (/du-an/ban-do) chi ghim dung cho khi du an co toa do RIENG.
 * Truoc day du an nao chua nhap vi do/kinh do thi lay tam tinh/thanh, nen ca
 * ba du an o Thai Nguyen deu roi vao mot diem giua tinh - nhin ra "3 du an"
 * chu khong ra tung du an mot.
 *
 * Lenh nay hoi Nominatim (dich vu tra cuu dia chi cua OpenStreetMap, mien
 * phi, khong can API key) de:
 *   - Tim toa do tung du an theo dia chi / ten du an, va doan luon phuong/xa
 *     tu ket qua tra ve.
 *   - Voi --xa: nap toa do cho phuong/xa cua nhung tinh dang co du an. Hai
 *     cot vn_wards.lat/lng nay con duoc luat "cach noi lam viec >= 30km" cua
 *     trang kiem tra dieu kien dung toi, nap vao la cho do chinh xac theo.
 *
 * LUU Y ve Nominatim: dich vu cong cong, quy dinh toi da MOT lan goi moi
 * giay va phai khai bao User-Agent that. Lenh da tu cho giua hai lan goi -
 * dung ha --cho xuong, ho chan IP thi ca trang mat tra cuu.
 *
 *     php artisan noxh:toa-do --thu     # xem truoc, khong ghi gi
 *     php artisan noxh:toa-do
 *     php artisan noxh:toa-do --xa      # nap them toa do phuong/xa
 */
class NapToaDo extends Command
{
    protected $signature = 'noxh:toa-do
                            {--thu : Chi in ra se lam gi, khong ghi vao CSDL}
                            {--lai : Tinh lai ca nhung du an da co toa do rieng}
                            {--xa : Nap them toa do cho phuong/xa cua cac tinh dang co du an}
                            {--cho=1100 : So mili giay cho giua hai lan goi (toi thieu 1000)}';

    protected $description = 'Nap toa do that cho du an (va phuong/xa) tu OpenStreetMap';

    private const API = 'https://nominatim.openstreetmap.org/search';

    /** Cac o trong phan address cua Nominatim co the chua ten phuong/xa. */
    private const O_XA = [
        'suburb', 'quarter', 'neighbourhood', 'historic', 'village', 'hamlet',
        'town', 'city_district', 'municipality', 'borough',
    ];

    /**
     * Ban kinh toi da khi gan du an vao phuong/xa GAN NHAT (km).
     *
     * Xa hon muc nay thi diem tim duoc gan nhu chac chan khong phai du an
     * dang tim, gan bua mot phuong/xa vao chi lam sai them.
     */
    private const XA_NHAT_KM = 20;

    /** Ban kinh chap nhan duoc khi du an chua khai phuong/xa (km). */
    private const TINH_KM = 90;

    private int $cho = 1100;
    private bool $thu = false;

    public function handle(): int
    {
        $this->thu = (bool) $this->option('thu');
        $this->cho = max(1000, (int) $this->option('cho'));

        if ($this->thu) {
            $this->warn('Chay thu: khong ghi gi vao CSDL.');
        }

        if ($this->option('xa')) {
            $this->napXa();
        }

        $this->napDuAn();

        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------

    /**
     * Toa do cho phuong/xa cua nhung tinh dang co du an.
     *
     * Khong nap ca 3321 phuong/xa cua ca nuoc: Nominatim la dich vu cong
     * cong, nap hang loat nhu vay la dung sai muc dich ho cho phep.
     */
    private function napXa(): void
    {
        $tinh = DB::table('products')->where('publish', 2)->whereNull('deleted_at')
            ->whereNotNull('province_code')->distinct()->pluck('province_code');

        $ds = DB::table('vn_wards as w')
            ->leftJoin('vn_provinces as p', 'p.code', '=', 'w.province_code')
            ->whereIn('w.province_code', $tinh)
            ->whereNull('w.lat')
            ->get(['w.code', 'w.name', 'p.name as tinh']);

        if ($ds->isEmpty()) {
            $this->info('Phuong/xa cua cac tinh co du an da du toa do.');
            return;
        }

        $this->info("Nap toa do cho {$ds->count()} phuong/xa (khoang " . ceil($ds->count() * $this->cho / 1000) . " giay)...");

        $thanh = 0;
        $thanhCong = $this->output->createProgressBar($ds->count());

        foreach ($ds as $x) {
            $ket = $this->hoi($x->name . ', ' . nx_ten_dia_gioi_ngan($x->tinh));
            $thanhCong->advance();

            if (!$ket) {
                continue;
            }

            if (!$this->thu) {
                DB::table('vn_wards')->where('code', $x->code)->update([
                    'lat' => $ket['lat'],
                    'lng' => $ket['lng'],
                ]);
            }

            $thanh++;
        }

        $thanhCong->finish();
        $this->newLine(2);
        $this->info("Da nap toa do cho {$thanh}/{$ds->count()} phuong/xa.");
    }

    /**
     * Toa do rieng cho tung du an.
     *
     * Bo qua du an da co toa do do NGUOI nhap - chi tinh lai cho du an chua
     * co, hoac dang muon tam tinh/thanh (toa do trung khit tam tinh la dau
     * hieu cua gia tri du phong, khong phai vi tri that).
     */
    private function napDuAn(): void
    {
        $ds = DB::table('products as p')
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'p.id')->where('pl.language_id', '=', 1);
            })
            ->leftJoin('vn_provinces as pr', 'pr.code', '=', 'p.province_code')
            ->leftJoin('vn_wards as w', 'w.code', '=', 'p.ward_code')
            ->where('p.publish', 2)->whereNull('p.deleted_at')
            ->whereNotNull('p.province_code')
            ->get([
                'p.id', 'p.address', 'p.latitude', 'p.longitude', 'p.province_code', 'p.ward_code',
                'pl.name', 'pr.name as tinh', 'pr.lat as tinh_lat', 'pr.lng as tinh_lng',
                'w.lat as xa_lat', 'w.lng as xa_lng', 'w.name as xa',
            ]);

        $sua = 0;
        $boQua = 0;

        foreach ($ds as $d) {
            if (!$this->option('lai') && !$this->toaDoDuPhong($d)) {
                $boQua++;
                continue;
            }

            $ket = $this->timDuAn($d);

            if (!$ket) {
                $this->warn("Khong tim duoc toa do: {$d->name}");
                continue;
            }

            $doi = ['latitude' => $ket['lat'], 'longitude' => $ket['lng'], 'updated_at' => now()];

            // Nominatim tra ve ca ten phuong/xa doi voi diem vua tim. Du an
            // chua chon phuong/xa thi lay luon, do la du lieu that chu khong
            // phai gan dai mot ma nao do.
            if (!$d->ward_code && ($ma = $this->doanXa($ket, $d->province_code))) {
                $doi['ward_code'] = $ma;
            }

            $this->line(sprintf(
                '  %-28s -> %s, %s%s',
                Str::limit($d->name, 28),
                $ket['lat'],
                $ket['lng'],
                isset($doi['ward_code']) ? '  (' . $ket['tenXa'] . ')' : ''
            ));

            if (!$this->thu) {
                DB::table('products')->where('id', $d->id)->update($doi);
            }

            $sua++;
        }

        $this->info("Da nap toa do cho {$sua} du an, bo qua {$boQua} du an da co toa do rieng.");
    }

    /**
     * Du an dang dung toa do du phong (rong, hoac trung tam tinh/thanh)?
     *
     * So den 4 chu so sau dau phay - khoang 11m, du chat de phan biet "chep
     * y tam tinh" voi "toa do that tinh co o gan".
     */
    private function toaDoDuPhong($d): bool
    {
        if ($d->latitude === null || $d->longitude === null) {
            return true;
        }

        if ($d->tinh_lat === null) {
            return false;
        }

        return round((float) $d->latitude, 4) === round((float) $d->tinh_lat, 4)
            && round((float) $d->longitude, 4) === round((float) $d->tinh_lng, 4);
    }

    /**
     * Tim toa do cua mot du an.
     *
     * Hoi Nominatim theo dia chi roi den ten du an - day moi la toa do
     * RIENG cua tung du an, dieu ca trang ban do can. Ket qua phai nam gan
     * phuong/xa (hoac tinh) da khai, neu khong thi coi nhu tra cuu lac: mot
     * cai ten trung o dau kia dat nuoc con te hon la khong tim thay.
     *
     * Lac het thi lui ve tam phuong/xa - van sat hon han tam tinh/thanh.
     */
    private function timDuAn($d): ?array
    {
        $tinh = nx_ten_dia_gioi_ngan($d->tinh);

        $moc = $d->xa_lat !== null ? [(float) $d->xa_lat, (float) $d->xa_lng] : null;
        $banKinh = self::XA_NHAT_KM;

        if (!$moc && $d->tinh_lat !== null) {
            $moc = [(float) $d->tinh_lat, (float) $d->tinh_lng];
            $banKinh = self::TINH_KM;
        }

        foreach ([$d->address, $d->name] as $cau) {
            $cau = trim((string) $cau);

            if ($cau === '') {
                continue;
            }

            // Dia chi mau hay ghi "Tuc-Duyen" thay cho "Tuc Duyen"; gach noi
            // lam Nominatim khong nhan ra dia danh.
            $ket = $this->hoi(str_replace('-', ' ', $cau) . ', ' . $tinh);

            if (!$ket) {
                continue;
            }

            $km = $moc ? NoxhKhoangCach::km($moc, [$ket['lat'], $ket['lng']]) : null;

            if ($km === null || $km <= $banKinh) {
                return $ket;
            }

            $this->warn(sprintf('  Bo ket qua lac %0.0fkm cho "%s": %s', $km, $d->name, $ket['tenXa'] ?: '?'));
        }

        if ($d->ward_code && $d->xa_lat !== null) {
            return ['lat' => (float) $d->xa_lat, 'lng' => (float) $d->xa_lng, 'diaChi' => [], 'tenXa' => $d->xa];
        }

        return null;
    }

    /**
     * Doi ket qua Nominatim thanh mot ma phuong/xa trong vn_wards.
     *
     * Hai duong: khop theo TEN truoc (chac chan nhat), khong duoc thi lay
     * phuong/xa GAN NHAT theo toa do. Ten OSM dung co the la ten truoc khi
     * sap xep lai don vi hanh chinh nam 2025, nen duong thu hai hay cuu
     * duoc nhung truong hop ten khong con trong danh muc.
     */
    private function doanXa(array $ket, string $maTinh): ?string
    {
        $xa = DB::table('vn_wards')->where('province_code', $maTinh)->get(['code', 'name', 'lat', 'lng']);

        foreach (self::O_XA as $o) {
            if (empty($ket['diaChi'][$o])) {
                continue;
            }

            $goc = $this->gon($ket['diaChi'][$o]);

            foreach ($xa as $x) {
                if (in_array($this->gon($x->name), [$goc, 'phuong ' . $goc, 'xa ' . $goc, 'thi tran ' . $goc], true)) {
                    return $x->code;
                }
            }
        }

        $gan = null;
        $xaNhat = self::XA_NHAT_KM;

        foreach ($xa as $x) {
            if ($x->lat === null || $x->lng === null) {
                continue;
            }

            $km = NoxhKhoangCach::km(
                [(float) $ket['lat'], (float) $ket['lng']],
                [(float) $x->lat, (float) $x->lng]
            );

            if ($km !== null && $km < $xaNhat) {
                $xaNhat = $km;
                $gan = $x->code;
            }
        }

        return $gan;
    }

    /** Mot lan goi Nominatim, da cho du thoi gian theo quy dinh cua ho. */
    private function hoi(string $cau): ?array
    {
        usleep($this->cho * 1000);

        try {
            $tl = Http::withHeaders([
                // Nominatim tu choi phuc vu neu khong biet ai dang goi.
                'User-Agent' => 'NOXH.vn ban do du an (' . config('app.url') . ')',
            ])->timeout(20)->get(self::API, [
                'format' => 'jsonv2',
                'limit' => 1,
                'countrycodes' => 'vn',
                'addressdetails' => 1,
                'accept-language' => 'vi',
                'q' => $cau,
            ]);
        } catch (\Throwable $e) {
            $this->warn('Goi Nominatim that bai: ' . $e->getMessage());
            return null;
        }

        if (!$tl->successful() || !($dong = $tl->json(0))) {
            return null;
        }

        return [
            'lat' => round((float) $dong['lat'], 7),
            'lng' => round((float) $dong['lon'], 7),
            'diaChi' => $dong['address'] ?? [],
            'tenXa' => $dong['address']['suburb'] ?? ($dong['address']['quarter'] ?? ''),
        ];
    }

    private function gon(string $s): string
    {
        return trim(preg_replace('/\s+/', ' ', str_replace('-', ' ', Str::lower(Str::ascii($s)))));
    }
}
