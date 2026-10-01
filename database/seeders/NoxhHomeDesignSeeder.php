<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nap noi dung cho cac o quan tri MOI them khi dung lai trang chu theo ban
 * thiet ke noxh_image/home-fix.jpg.
 *
 * Nhung thu truoc day ghi cung trong ma nguon (chu tren nut, ba diem nhan
 * cua dai kiem tra dieu kien, cac khoang gia trong o tim, mau nhan cua
 * chuyen muc tin tuc, tieu de tung khoi) gio deu la o quan tri sua duoc.
 * Seeder nay dat gia tri ban dau cho chung.
 *
 * Chay lai nhieu lan khong sao: dung updateOrInsert theo khoa.
 *
 *     php artisan db:seed --class=NoxhHomeDesignSeeder --force
 */
class NoxhHomeDesignSeeder extends Seeder
{
    public function run(): void
    {
        $this->napChu();
        $this->napCauHinh();
        $this->napMauChuyenMuc();
        $this->napMenuChan();

        $this->command?->info('Da nap noi dung trang chu theo ban thiet ke moi.');
    }

    // ------------------------------------------------------------------ chu
    private function napChu(): void
    {
        $chu = [
            // --- thuong hieu, dau trang, chan trang -------------------------
            'brand_tagline' => 'Rõ pháp lý · Đúng thông tin · Vì an cư',
            'header_search_placeholder' => 'Tìm dự án, tin tức...',
            'header_phone_note' => 'Tư vấn miễn phí 24/7',
            'footer_contact_heading' => 'Liên hệ',
            'footer_social_heading' => 'Kết nối với chúng tôi',
            'footer_top_text' => 'Lên đầu trang',
            'footer_slogan' => 'Vì cộng đồng · Vì một Việt Nam an cư',

            // --- banner ------------------------------------------------------
            'hero_label' => 'Cổng thông tin',
            'hero_title' => 'Nhà ở xã hội',
            'hero_slogan' => 'Rõ pháp lý - Đúng thông tin - Chọn đúng nhà',
            'hero_usp_1' => 'Thông tin chính thống',
            'hero_usp_2' => 'Cập nhật nhanh chóng',
            'hero_usp_3' => 'Kết nối tư vấn toàn quốc',
            'hero_btn_1_text' => 'TÌM DỰ ÁN NGAY',
            'hero_btn_1_url' => 'du-an',
            'hero_btn_2_text' => 'KIỂM TRA ĐIỀU KIỆN',
            'hero_btn_2_url' => 'kiem-tra-dieu-kien',

            // --- bang so lieu canh banner ------------------------------------
            'stat_1_icon' => 'building',
            'stat_1_label' => 'Dự án NOXH cập nhật',
            'stat_2_icon' => 'pin',
            'stat_2_label' => 'Tỉnh thành trên toàn quốc',
            'stat_3_icon' => 'users',
            'stat_3_label' => 'Người đã được tư vấn',
            'stat_4_icon' => 'shield-check',
            'stat_4_value' => '100%',
            'stat_4_label' => 'Thông tin có nguồn gốc',

            // --- thanh tim du an ---------------------------------------------
            'search_title' => 'Tìm dự án nhà ở xã hội',
            'search_description' => 'Chọn khu vực để xem dự án phù hợp',
            'search_button' => 'TÌM DỰ ÁN',
            'search_price_ranges' => implode("\n", [
                'Dưới 18 triệu/m² | -18',
                '18 - 20 triệu/m² | 18-20',
                '20 - 22 triệu/m² | 20-22',
                'Trên 22 triệu/m² | 22-',
            ]),

            // --- dai moi kiem tra dieu kien -----------------------------------
            'check_title' => 'Bạn có đủ điều kiện mua NOXH?',
            'check_description' => 'Trả lời 8 câu hỏi - Chỉ mất khoảng 3 phút - Nhận kết quả ngay',
            'check_button_text' => 'KIỂM TRA NGAY',
            'check_button_url' => 'kiem-tra-dieu-kien',
            'check_point_1' => 'Nhanh chóng',
            'check_point_1_icon' => 'clock',
            'check_point_2' => 'Chính xác',
            'check_point_2_icon' => 'shield-check',
            'check_point_3' => 'Bảo mật thông tin',
            'check_point_3_icon' => 'lock',

            // --- khoi du an noi bat -------------------------------------------
            'project_block_heading' => 'Dự án nhà ở xã hội nổi bật',
            'project_more_text' => 'Xem tất cả dự án',

            // --- sau o thong tin huu ich ---------------------------------------
            'useful_heading' => 'Thông tin hữu ích',
            'useful_1_title' => 'Chính sách & pháp luật',
            'useful_1_url' => 'phap-ly-noxh',
            'useful_1_icon' => 'scale',
            'useful_2_title' => 'Hướng dẫn hồ sơ',
            'useful_2_url' => 'ho-so/can-chuan-bi',
            'useful_2_icon' => 'clipboard',
            'useful_3_title' => 'Tài chính & vay vốn ngân hàng',
            'useful_3_url' => 'tai-chinh/tinh-khoan-vay',
            'useful_3_icon' => 'coins',
            'useful_4_title' => 'Kinh nghiệm mua NOXH',
            'useful_4_url' => 'tin-tuc',
            'useful_4_icon' => 'bulb',
            'useful_5_title' => 'Câu hỏi thường gặp',
            'useful_5_url' => 'hoi-dap',
            'useful_5_icon' => 'question',
            'useful_6_title' => 'Biểu mẫu - Tải về',
            'useful_6_url' => 'ho-so/mau-don',
            'useful_6_icon' => 'download',

            // --- khoi tin tuc ---------------------------------------------------
            'news_block_heading' => 'Tin tức mới nhất',
            'news_more_text' => 'Xem tất cả',

            // --- doi tu van ------------------------------------------------------
            'advisor_heading' => 'Tư vấn hồ sơ tại khu vực của bạn',
            'advisor_description' => 'Đội ngũ tư vấn được NOXH.vn xác minh - Hỗ trợ tận tâm - Hoàn toàn miễn phí',
            'advisor_more_text' => 'Xem tất cả tư vấn viên',
            'advisor_role' => 'Tư vấn hồ sơ NOXH',

            // --- dai dang ky nhan tin ---------------------------------------------
            'subscribe_title' => 'Đăng ký nhận thông tin mới nhất',
            'subscribe_description' => 'Cập nhật dự án, chính sách và cơ hội mua NOXH phù hợp với bạn',
            'subscribe_button' => 'ĐĂNG KÝ NGAY',
            'subscribe_note' => 'Thông tin của bạn được bảo mật tuyệt đối.',
        ];

        foreach ($chu as $khoa => $noiDung) {
            DB::table('introduces')->updateOrInsert(
                ['keyword' => $khoa, 'language_id' => 1],
                ['content' => $noiDung, 'user_id' => 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    // ------------------------------------------------------------- cau hinh
    /**
     * Ten thuong hieu hien canh logo.
     *
     * Ban cai nay von la website TRUC GPS nen o do con de "TRUC+" - dau
     * trang va chan trang lay thang o nay ra nen phai sua.
     */
    private function napCauHinh(): void
    {
        $cauHinh = [
            'homepage_brand' => 'NOXH.vn',
            'homepage_company' => 'NOXH.vn',
        ];

        foreach ($cauHinh as $khoa => $noiDung) {
            DB::table('systems')->updateOrInsert(
                ['keyword' => $khoa, 'language_id' => 1],
                ['content' => $noiDung, 'user_id' => 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    // ---------------------------------------------------- mau nhan chuyen muc
    /**
     * Mau nhan cua tung chuyen muc tin tuc.
     *
     * Chi dat cho chuyen muc CHUA co mau - quan tri doi roi thi khong ghi de
     * len lua chon cua ho.
     */
    private function napMauChuyenMuc(): void
    {
        if (!Schema::hasColumn('post_catalogues', 'color')) {
            return;
        }

        $mau = [
            'chinh-sach' => '#e0452c',
            'thi-truong' => '#0a78f5',
            'huong-dan-ho-so' => '#1e93cf',
            'kinh-nghiem' => '#b8860b',
            'cau-chuyen-an-cu' => '#8e44ad',
            'tin-dia-phuong' => '#3e8c4a',
        ];

        foreach ($mau as $duongDan => $ma) {
            $id = DB::table('post_catalogue_language')
                ->where('canonical', $duongDan)
                ->where('language_id', 1)
                ->value('post_catalogue_id');

            if ($id) {
                DB::table('post_catalogues')->where('id', $id)->whereNull('color')
                    ->update(['color' => $ma]);
            }
        }
    }

    // ------------------------------------------------------------- menu chan
    /**
     * Chia nhom footer-menu thanh ba cot co tieu de, dung nhu chan trang o
     * ban ve noxh_image/tin-tuc-fix.webp: "Ve chung toi", "Huong dan",
     * "Ho tro". Cot thu tu ("Lien he") KHONG nam o day - so dien thoai, email
     * va dia chi doc thang Cau hinh he thong.
     *
     * Ban ve in "Dieu khoan" / "Bao mat" o cot mot va "Dieu khoan su dung" /
     * "Chinh sach bao mat" o cot ba - cung mot trang goi bang hai ten. O day
     * moi trang chi dat MOT cho: hai dong dan cung mot noi trong mot chan
     * trang chi lam nguoi doc phan van.
     *
     * Muc nao chua co trong nhom thi them moi, khong xoa muc nao dang co.
     */
    private function napMenuChan(): void
    {
        $nhomId = DB::table('menu_catalogues')->where('keyword', 'footer-menu')->value('id');

        if (!$nhomId) {
            return;
        }

        // tieu de cot => [ten muc => duong dan]
        $cot = [
            'Về chúng tôi' => [
                'Giới thiệu' => 'gioi-thieu',
                'Điều khoản sử dụng' => 'dieu-khoan-su-dung',
                'Chính sách bảo mật' => 'chinh-sach-bao-mat',
                'Liên hệ' => 'lien-he',
            ],
            'Hướng dẫn' => [
                'Điều kiện mua NOXH' => 'phap-ly-noxh/dieu-kien',
                'Hồ sơ cần chuẩn bị' => 'ho-so/can-chuan-bi',
                'Quy trình mua NOXH' => 'phap-ly-noxh/chinh-sach',
                'Câu hỏi thường gặp' => 'hoi-dap',
            ],
            'Hỗ trợ' => [
                'Kiểm tra điều kiện' => 'kiem-tra-dieu-kien',
                'Văn bản pháp luật' => 'phap-ly-noxh/van-ban',
                'Sitemap' => 'sitemap',
            ],
        ];

        $thuTu = 1;

        foreach ($cot as $ten => $muc) {
            $chaId = $this->mucCha($nhomId, $ten, $thuTu++);

            foreach ($muc as $tenCon => $duongDan) {
                $conId = DB::table('menus as m')
                    ->join('menu_language as ml', function ($join) {
                        $join->on('ml.menu_id', '=', 'm.id')->where('ml.language_id', '=', 1);
                    })
                    ->where('m.menu_catalogue_id', $nhomId)
                    ->where('ml.canonical', $duongDan)
                    ->value('m.id');

                if (!$conId) {
                    $conId = $this->themMuc($nhomId, $tenCon, $duongDan, $thuTu);
                }

                DB::table('menus')->where('id', $conId)
                    ->update(['parent_id' => $chaId, 'lft' => $thuTu++, 'level' => 1, 'publish' => 2]);
            }
        }
    }

    /** Them mot muc con moi vao nhom menu, tra ve id. */
    private function themMuc(int $nhomId, string $ten, string $duongDan, int $thuTu): int
    {
        $id = DB::table('menus')->insertGetId([
            'menu_catalogue_id' => $nhomId,
            'parent_id' => 0,
            'lft' => $thuTu,
            'rgt' => $thuTu,
            'level' => 1,
            'order' => $thuTu,
            'publish' => 2,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('menu_language')->insert([
            'menu_id' => $id,
            'language_id' => 1,
            'name' => $ten,
            'canonical' => $duongDan,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) $id;
    }

    /** Tim (hoac tao) mot muc cha khong co duong dan, chi lam tieu de cot. */
    private function mucCha(int $nhomId, string $ten, int $thuTu): int
    {
        $id = DB::table('menus as m')
            ->join('menu_language as ml', function ($join) {
                $join->on('ml.menu_id', '=', 'm.id')->where('ml.language_id', '=', 1);
            })
            ->where('m.menu_catalogue_id', $nhomId)
            ->where('ml.name', $ten)
            ->value('m.id');

        if (!$id) {
            $id = DB::table('menus')->insertGetId([
                'menu_catalogue_id' => $nhomId,
                'parent_id' => 0,
                'lft' => $thuTu,
                'rgt' => $thuTu,
                'level' => 0,
                'order' => $thuTu,
                'publish' => 2,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('menu_language')->insert([
                'menu_id' => $id,
                'language_id' => 1,
                // Tieu de cot khong tro di dau - de duong dan rong.
                'name' => $ten,
                'canonical' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('menus')->where('id', $id)->update(['parent_id' => 0, 'lft' => $thuTu, 'publish' => 2]);

        return (int) $id;
    }
}
