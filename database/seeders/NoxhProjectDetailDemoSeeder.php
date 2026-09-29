<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DU LIEU MAU cho trang chi tiet du an.
 *
 * Tach rieng khoi NoxhProjectDetailSeeder (noi dung quan tri that) vi day la
 * du lieu DUNG DE XEM THU: cac loai can ho, o tien ich va may duong dan anh
 * tro tam vao anh mac dinh. Chay khi can dung thu giao dien, khong chay tren
 * ban that.
 *
 *     php artisan db:seed --class=NoxhProjectDetailDemoSeeder --force
 *
 * Chi them cho du an CHUA co du lieu, chay lai khong nhan doi.
 */
class NoxhProjectDetailDemoSeeder extends Seeder
{
    private const ANH = '/uploads/noxh/du-an-mac-dinh-the.jpg';
    private const ANH_PHU = '/uploads/noxh/du-an-mac-dinh.jpg';

    public function run(): void
    {
        $duAn = DB::table('products')->whereNull('deleted_at')->pluck('id');

        if ($duAn->isEmpty()) {
            $this->command?->warn('Chua co du an nao.');
            return;
        }

        $themCan = 0;
        $themIch = 0;
        $themAnh = 0;
        $themNguoi = 0;

        // Gan nhan vien cho MOT SO du an thoi, khong gan het: phai con du an
        // trong de con nhin duoc khoi "chua phan cong ai" - va de cac bai
        // kiem tra tu dong tim duoc mot du an chua co ai phu trach.
        $coNguoi = $duAn->take(max(1, (int) ceil($duAn->count() / 2)));

        foreach ($duAn as $id) {
            $themCan += $this->loaiCanHo($id);
            $themIch += $this->tienIch($id);
            $themAnh += $this->anhMau($id);
            $this->toaDo($id);

            if ($coNguoi->contains($id)) {
                $themNguoi += $this->nhanVien($id);
            }
        }

        $this->command?->info("Du lieu mau: {$themCan} loai can ho, {$themIch} o tien ich, {$themAnh} du an duoc gan anh, {$themNguoi} luot gan nhan vien.");
    }

    private function loaiCanHo(int $duAnId): int
    {
        if (DB::table('project_units')->where('product_id', $duAnId)->exists()) {
            return 0;
        }

        $mau = [
            ['Căn 1PN - 1WC', 19.55, 21, 1.075, 1.180, "Phù hợp người độc thân\nTối ưu công năng"],
            ['Căn 2PN - 1WC', 25, 28, 1.180, 1.260, "Phù hợp gia đình trẻ\nKhông gian thoáng"],
            ['Căn 2PN - 2WC', 32, 36, 1.280, 1.420, "Không gian rộng rãi\nPhù hợp gia đình 3–4 người"],
        ];

        foreach ($mau as $i => $m) {
            DB::table('project_units')->insert([
                'product_id' => $duAnId,
                'name' => $m[0],
                'image' => self::ANH,
                'area_from' => $m[1],
                'area_to' => $m[2],
                'price_from' => $m[3],
                'price_to' => $m[4],
                'price_unit' => 'tỷ',
                'bullets' => $m[5],
                'publish' => 2,
                'order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return count($mau);
    }

    private function tienIch(int $duAnId): int
    {
        if (DB::table('project_highlights')->where('product_id', $duAnId)->where('group', 'amenity')->exists()) {
            return 0;
        }

        $mau = [
            ['grid', 'Công viên', 'nội khu'],
            ['users', 'Trường học', 'liên cấp'],
            ['house', 'Siêu thị', 'tiện ích'],
            ['shield-check', 'An ninh', '24/7'],
        ];

        foreach ($mau as $i => $m) {
            DB::table('project_highlights')->insert([
                'product_id' => $duAnId,
                'group' => 'amenity',
                'icon' => $m[0],
                'title' => $m[1],
                'subtitle' => $m[2],
                'order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return count($mau);
    }

    /**
     * Gan tam anh mac dinh vao cac o anh moi va dung chinh no lam album, de
     * con nhin duoc bo cuc. Chua co anh that thi trang van dung, chi la thieu
     * mat vai khoi.
     */
    private function anhMau(int $duAnId): int
    {
        $d = DB::table('products')->where('id', $duAnId)
            ->first(['album', 'site_plan_image', 'map_image', 'progress_image']);

        if (!$d) {
            return 0;
        }

        $sua = ['updated_at' => now()];

        foreach (['site_plan_image', 'map_image', 'progress_image'] as $o) {
            if (empty($d->$o)) {
                $sua[$o] = self::ANH;
            }
        }

        if (empty($d->album)) {
            // Xen ke hai anh: dai anh nho tu bo nhung anh TRUNG voi anh dai
            // dien, nen album toan mot anh giong anh dai dien se ra rong.
            // Dai anh nho chi ve 5 o, o cuoi mang chu "+ N anh"; de du 12 anh
            // cho con nhin thay con so do.
            $sua['album'] = json_encode(array_merge(
                array_fill(0, 11, self::ANH_PHU),
                [self::ANH]
            ));
        }

        if (count($sua) === 1) {
            return 0;
        }

        DB::table('products')->where('id', $duAnId)->update($sua);

        return 1;
    }

    /**
     * Toa do tam cho du an chua co.
     *
     * Lay tam tinh/thanh trong vn_provinces - chua dung dia chi du an nhung
     * du de nut "Xem tren Google Maps" tro dung khu vuc, va de thay khoi Vi
     * tri hoat dong that.
     */
    private function toaDo(int $duAnId): int
    {
        $d = DB::table('products')->where('id', $duAnId)
            ->first(['latitude', 'longitude', 'province_code']);

        if (!$d || $d->latitude !== null || !$d->province_code) {
            return 0;
        }

        $tinh = DB::table('vn_provinces')->where('code', $d->province_code)->first(['lat', 'lng']);

        if (!$tinh || $tinh->lat === null) {
            return 0;
        }

        DB::table('products')->where('id', $duAnId)->update([
            'latitude' => $tinh->lat,
            'longitude' => $tinh->lng,
            'updated_at' => now(),
        ]);

        return 1;
    }

    /**
     * Gan nhan vien kinh doanh vao du an.
     *
     * Khoi "Danh sach tu van ho tro" o trang chi tiet lay dung nhung nguoi
     * duoc gan o day - khong gan ai thi khoi do chi hien loi moi de lai so
     * dien thoai.
     */
    private function nhanVien(int $duAnId): int
    {
        if (DB::table('product_user')->where('product_id', $duAnId)->exists()) {
            return 0;
        }

        $nguoi = DB::table('users as u')
            ->join('user_catalogues as uc', 'uc.id', '=', 'u.user_catalogue_id')
            ->where('uc.is_sale', 1)
            ->where('u.publish', 2)
            ->whereNull('u.deleted_at')
            ->orderBy('u.id')
            ->limit(6)
            ->pluck('u.id');

        foreach ($nguoi as $i => $id) {
            DB::table('product_user')->insert([
                'product_id' => $duAnId,
                'user_id' => $id,
                'order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $nguoi->count();
    }
}
