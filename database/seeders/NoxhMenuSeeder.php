<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Menu dieu huong NOXH.vn, dung theo so do da chot.
 *
 * Thay bo menu con sot cua website truoc (22 muc main-menu + 13 muc
 * footer-menu, deu la muc cua trang phu kien o to).
 *
 * So do:
 *   TRANG CHU
 *   DU AN            -> Danh sach du an / Theo tinh thanh / Chi tiet du an
 *   KIEM TRA DIEU KIEN -> Cau hoi / Nhap SDT / Ket qua
 *   PHONG PHAP LY    -> Doi tuong / Dieu kien / Chinh sach / Hoi dap phap ly
 *   HO SO            -> Ho so can chuan bi / Mau don / Checklist
 *   TAI CHINH        -> Tinh khoan vay / Kha nang tai chinh
 *   HOI DAP
 *   TIN TUC
 *   CONG HOA         -> Tu van
 *
 * Ghi chu ve hai muc con dac biet:
 *  - "Chi tiet du an" khong phai mot trang co dinh ma la trang cua tung du an.
 *    Giu trong menu de sau nay lam breadcrumb, nhung dat publish = 1 (an) de
 *    khong hien ra thanh mot muc bam duoc.
 *  - "Cau hoi / Nhap SDT / Ket qua" la ba buoc cua cung mot luong, khong phai
 *    ba trang doc lap. Cung dat publish = 1, giu de biet duong dan tung buoc.
 *
 * Chay: php artisan db:seed --class=NoxhMenuSeeder --force
 * Chay lai se xoa va nap lai dung hai nhom main-menu va footer-menu.
 */
class NoxhMenuSeeder extends Seeder
{
    private const LANG = 1;

    /**
     * Bo dem lft tam thoi.
     *
     * Nestedsetbie::Get() sap xep theo tb1.lft (bo qua tham so truyen vao). Neu
     * chen tat ca voi lft = 0 thi MySQL tra ve thu tu tuy y, va cay dung lai se
     * dao lung tung - da gap: "Trang chu" nhay vao giua, cac muc con dao nguoc.
     * Vi vay phai chen san lft tang dan theo dung thu tu mong muon.
     */
    private int $lftCounter = 1;

    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('=== Nap menu NOXH.vn ===');

        // [ten, duong dan, hien hay an, [cac muc con]]
        $tree = [
            ['Trang chủ', '', true, []],
            ['Dự án', 'du-an', true, [
                ['Danh sách dự án', 'du-an', true],
                ['Theo tỉnh/thành', 'du-an/tinh-thanh', true],
                ['Chi tiết dự án', 'du-an/chi-tiet', false],
            ]],
            ['Kiểm tra điều kiện', 'kiem-tra-dieu-kien', true, [
                ['Câu hỏi', 'kiem-tra-dieu-kien/cau-hoi', false],
                ['Nhập số điện thoại', 'kiem-tra-dieu-kien/xac-nhan', false],
                ['Kết quả', 'kiem-tra-dieu-kien/ket-qua', false],
            ]],
            ['Phòng pháp lý', 'phap-ly-noxh', true, [
                ['Đối tượng', 'phap-ly-noxh/doi-tuong', true],
                ['Điều kiện', 'phap-ly-noxh/dieu-kien', true],
                ['Chính sách', 'phap-ly-noxh/chinh-sach', true],
                ['Hỏi đáp pháp lý', 'phap-ly-noxh/hoi-dap', true],
            ]],
            ['Hồ sơ', 'ho-so', true, [
                ['Hồ sơ cần chuẩn bị', 'ho-so/can-chuan-bi', true],
                ['Mẫu đơn', 'ho-so/mau-don', true],
                ['Checklist', 'ho-so/checklist', true],
            ]],
            ['Tài chính', 'tai-chinh', true, [
                ['Tính khoản vay', 'tai-chinh/tinh-khoan-vay', true],
                ['Khả năng tài chính', 'tai-chinh/kha-nang-tai-chinh', true],
            ]],
            ['Hỏi đáp', 'hoi-dap', true, []],
            ['Tin tức', 'tin-tuc', true, []],
            ['Công Hòa', 'cong-hoa', true, [
                ['Tư vấn', 'cong-hoa/tu-van', true],
            ]],
        ];

        $userId = (int) (DB::table('users')->min('id') ?? 1);
        $mainId = $this->catalogueId('main-menu');
        $footerId = $this->catalogueId('footer-menu');

        $this->lftCounter = 1;

        DB::transaction(function () use ($tree, $userId, $mainId, $footerId) {
            // Xoa menu cu cua hai nhom nay.
            $oldIds = DB::table('menus')
                ->whereIn('menu_catalogue_id', array_filter([$mainId, $footerId]))
                ->pluck('id');
            DB::table('menu_language')->whereIn('menu_id', $oldIds)->delete();
            DB::table('menus')->whereIn('id', $oldIds)->delete();

            $now = now();
            $soCap1 = 0;
            $soCap2 = 0;

            foreach ($tree as $i => [$name, $url, $show, $children]) {
                $parentId = $this->insertMenu($mainId, 0, 1, $i, $show, $name, $url, $userId, $now);
                $soCap1++;

                foreach ($children as $j => [$cName, $cUrl, $cShow]) {
                    $this->insertMenu($mainId, $parentId, 2, $j, $cShow, $cName, $cUrl, $userId, $now);
                    $soCap2++;
                }
            }

            // Footer lay cac muc thuong duoc tim: gon hon menu chinh.
            $footer = [
                ['Giới thiệu', 'gioi-thieu'],
                ['Điều kiện mua NOXH', 'phap-ly-noxh/dieu-kien'],
                ['Hồ sơ cần chuẩn bị', 'ho-so/can-chuan-bi'],
                ['Quy trình mua NOXH', 'phap-ly-noxh/chinh-sach'],
                ['Câu hỏi thường gặp', 'hoi-dap'],
                ['Chính sách bảo mật', 'chinh-sach-bao-mat'],
                ['Điều khoản sử dụng', 'dieu-khoan-su-dung'],
                ['Liên hệ', 'lien-he'],
            ];

            foreach ($footer as $i => [$name, $url]) {
                $this->insertMenu($footerId, 0, 1, $i, true, $name, $url, $userId, $now);
            }

            $this->command->line(sprintf('  menu chinh    : %d muc cap 1, %d muc cap 2', $soCap1, $soCap2));
            $this->command->line(sprintf('  menu chan trang: %d muc', count($footer)));
        });

        // Dung lai lft/rgt/level bang chinh class ma trang admin dung, de cay
        // khong lech so voi luc luu tay.
        $this->rebuildTree();

        $this->command->newLine();
    }

    private function catalogueId(string $keyword): ?int
    {
        $id = DB::table('menu_catalogues')->where('keyword', $keyword)->value('id');

        if ($id === null) {
            $id = DB::table('menu_catalogues')->insertGetId([
                'name' => $keyword,
                'keyword' => $keyword,
                'publish' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) $id;
    }

    private function insertMenu(
        int $catalogueId, int $parentId, int $level, int $order,
        bool $show, string $name, string $url, int $userId, $now
    ): int {
        $id = DB::table('menus')->insertGetId([
            'parent_id' => $parentId,
            'menu_catalogue_id' => $catalogueId,
            'lft' => $this->lftCounter++,
            'rgt' => 0,
            'level' => $level,
            // publish 2 = hien, 1 = an. Cac buoc trong luong va trang chi tiet
            // duoc giu lai de biet duong dan nhung khong hien thanh muc menu.
            'publish' => $show ? 2 : 1,
            'order' => $order,
            'user_id' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('menu_language')->insert([
            'menu_id' => $id,
            'language_id' => self::LANG,
            'name' => $name,
            'canonical' => $url,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $id;
    }

    private function rebuildTree(): void
    {
        $nestedset = new \App\Classes\Nestedsetbie([
            'table' => 'menus',
            'foreignkey' => 'menu_id',
            'isMenu' => true,
            'language_id' => self::LANG,
        ]);

        $nestedset->Get('level ASC, order ASC');
        $nestedset->Recursive(0, $nestedset->Set());
        $nestedset->Action();

        $this->command->line('  da dung lai lft/rgt/level cho bang menus');
    }
}
