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
 *     python tools/ve-anh-mau.py           # ve bo anh minh hoa truoc
 *     php artisan db:seed --class=NoxhProjectDetailDemoSeeder --force
 *
 * Chi them cho du an CHUA co du lieu, chay lai khong nhan doi.
 */
class NoxhProjectDetailDemoSeeder extends Seeder
{
    private const ANH = '/uploads/noxh/du-an-mac-dinh-the.jpg';
    private const ANH_PHU = '/uploads/noxh/du-an-mac-dinh.jpg';
    private const SO_DO = '/uploads/noxh/mat-bang-tong-the.jpg';
    private const BAN_DO = '/uploads/noxh/ban-do-mac-dinh.jpg';

    /** Anh mat bang tung loai can - ve tay bang tools, khong tai tren mang. */
    private const MAT_BANG = [
        '/uploads/noxh/mat-bang-1pn-1wc.jpg',
        '/uploads/noxh/mat-bang-2pn-1wc.jpg',
        '/uploads/noxh/mat-bang-2pn-2wc.jpg',
    ];

    /** Video mau - dung mot doan gioi thieu cong khai cua YouTube. */
    private const VIDEO = 'https://www.youtube.com/watch?v=aqz-KE-bpKQ';

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
        $themGiay = 0;
        $themNguoi = 0;

        // Gan nhan vien cho MOT SO du an thoi, khong gan het: phai con du an
        // trong de con nhin duoc khoi "chua phan cong ai" - va de cac bai
        // kiem tra tu dong tim duoc mot du an chua co ai phu trach.
        $coNguoi = $duAn->take(max(1, (int) ceil($duAn->count() / 2)));

        foreach ($duAn as $id) {
            $themCan += $this->loaiCanHo($id);
            $themIch += $this->tienIch($id);
            $themAnh += $this->anhMau($id);
            $themGiay += $this->giayTo($id);
            $this->toaDo($id);
            $this->tuongTu($id, $duAn);

            if ($coNguoi->contains($id)) {
                $themNguoi += $this->nhanVien($id);
            }
        }

        $this->command?->info("Du lieu mau: {$themCan} loai can ho, {$themIch} o tien ich, {$themGiay} giay to, {$themAnh} du an duoc gan anh, {$themNguoi} luot gan nhan vien.");
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
                'image' => self::MAT_BANG[$i] ?? self::ANH,
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
            ->first(['album', 'video_url', 'site_plan_image', 'map_image', 'progress_image']);

        if (!$d) {
            return 0;
        }

        $sua = ['updated_at' => now()];

        if (empty($d->site_plan_image)) {
            $sua['site_plan_image'] = self::SO_DO;
        }

        if (empty($d->map_image)) {
            $sua['map_image'] = self::BAN_DO;
        }

        if (empty($d->progress_image)) {
            $sua['progress_image'] = self::ANH;
        }

        if (empty($d->video_url)) {
            $sua['video_url'] = self::VIDEO;
        }

        if (empty($d->album)) {
            // Xen ke hai anh: dai anh nho tu bo nhung anh TRUNG voi anh dai
            // dien, nen album toan mot anh giong anh dai dien se ra rong.
            // Dai anh nho chi ve 5 o, o cuoi mang chu "+ N anh"; de 17 anh
            // cho ra dung con so "+ 12 anh" nhu ban thiet ke.
            $sua['album'] = json_encode(array_merge(
                array_fill(0, 12, self::ANH_PHU),
                array_fill(0, 5, self::SO_DO)
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
     * Giay to mau cho hai tab "Phap ly" va "Tai lieu".
     *
     * Hai tab do doc chung bang project_documents, khac nhau o cot group -
     * khong co dong nhom "doc" thi tab "Tai lieu" khong hien ra.
     */
    private function giayTo(int $duAnId): int
    {
        // Kiem tra tung nhom rieng: du an co san ho so phap ly tu truoc van
        // con thieu nhom "Tai lieu", ma thieu la mat han mot tab.
        $daCo = DB::table('project_documents')->where('product_id', $duAnId)
            ->distinct()->pluck('group')->all();

        $mau = [
            ['legal', 'Quyết định chấp thuận chủ trương đầu tư', '1234/QĐ-UBND', 'UBND tỉnh'],
            ['legal', 'Giấy chứng nhận quyền sử dụng đất', 'CX 123456', 'Sở Tài nguyên và Môi trường'],
            ['legal', 'Giấy phép xây dựng', '88/GPXD', 'Sở Xây dựng'],
            ['legal', 'Văn bản nghiệm thu phòng cháy chữa cháy', '45/NT-PCCC', 'Công an tỉnh'],
            ['doc', 'Bảng giá bán dự kiến', null, null],
            ['doc', 'Mẫu đơn đăng ký mua nhà ở xã hội', null, null],
            ['doc', 'Hướng dẫn hồ sơ vay gói ưu đãi', null, null],
        ];

        $them = 0;

        foreach ($mau as $i => $m) {
            if (in_array($m[0], $daCo, true)) {
                continue;
            }

            $them++;

            DB::table('project_documents')->insert([
                'product_id' => $duAnId,
                'group' => $m[0],
                'title' => $m[1],
                'doc_number' => $m[2],
                'issuer' => $m[3],
                'issued_date' => $m[0] === 'legal' ? now()->subMonths(6 + $i)->toDateString() : null,
                'publish' => 2,
                'order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $them;
    }

    /**
     * Chon san vai du an tuong tu, de khoi cuoi trang co cai ma xem.
     */
    private function tuongTu(int $duAnId, $duAn): int
    {
        if (DB::table('product_related')->where('product_id', $duAnId)->exists()) {
            return 0;
        }

        // Uu tien du an cung tinh: tieu de khoi co dau {tinh} nen mot du an
        // tinh khac nam duoi dong chu "tai Thai Nguyen" trong nhu loi.
        $tinh = DB::table('products')->where('id', $duAnId)->value('province_code');

        $khac = DB::table('products')->whereNull('deleted_at')
            ->where('id', '!=', $duAnId)
            ->when($tinh, fn ($q) => $q->orderByRaw('province_code = ? DESC', [$tinh]))
            ->orderBy('id')
            ->limit(3)
            ->pluck('id');

        foreach ($khac as $i => $id) {
            DB::table('product_related')->insert([
                'product_id' => $duAnId,
                'related_id' => $id,
                'order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $khac->count();
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
