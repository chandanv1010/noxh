<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Noi dung ban dau cho cac o quan tri MOI cua trang danh sach du an, dung
 * lai theo ban thiet ke noxh_image/project-cate-fix.jpg.
 *
 * Truoc day dai bon so lieu, chu tren nut, ba gach dau dong cua hai khoi
 * moi goi tu van va tieu de cot phai deu ghi cung trong Blade. Gio tat ca
 * nam trong module "Gioi thieu" (nhom "Khoi 4" va "Khoi 4b").
 *
 * Seeder CHI THEM o con trong: chay lai khong de len thu quan tri da sua.
 *
 *     php artisan db:seed --class=NoxhProjectPageSeeder --force
 */
class NoxhProjectPageSeeder extends Seeder
{
    public function run(): void
    {
        $chu = [
            // --- dau trang: tieu de va dai bon so lieu ------------------------
            'project_heading' => 'Danh sách dự án Nhà ở xã hội',
            'project_description' => "Cập nhật mới nhất các dự án NOXH trên toàn quốc.\nThông tin minh bạch – Pháp lý rõ ràng – Hỗ trợ tư vấn hồ sơ.",

            // {du_an} va {tinh} duoc thay bang so that luc hien trang.
            'project_stat_1_value' => '{du_an}',
            'project_stat_1_label' => 'Dự án toàn quốc',
            'project_stat_1_icon' => 'city',
            'project_stat_2_value' => '{tinh}',
            'project_stat_2_label' => 'Tỉnh / Thành phố',
            'project_stat_2_icon' => 'map-pins',
            'project_stat_3_value' => '15.250+',
            'project_stat_3_label' => 'Khách hàng quan tâm',
            'project_stat_3_icon' => 'group',
            'project_stat_4_value' => '100%',
            'project_stat_4_label' => 'Thông tin kiểm chứng',
            'project_stat_4_icon' => 'verified',

            // --- thanh tim kiem va bo loc ------------------------------------
            'project_search_placeholder' => 'Tìm kiếm dự án (ví dụ: Túc Duyên, Hà Nội, Thái Nguyên...)',
            'project_search_button' => 'Tìm kiếm',
            'project_sort_label' => 'Sắp xếp:',
            'project_filter_heading' => 'Lọc dự án',
            'project_filter_clear' => 'Xóa lọc',
            'project_filter_button' => 'ÁP DỤNG BỘ LỌC',

            // --- cot phai: ban do --------------------------------------------
            'projectaside_map_heading' => 'Bản đồ dự án',
            'projectaside_map_all_text' => 'Xem tất cả',
            'projectaside_map_more_text' => 'Xem thêm tỉnh thành',
            'projectaside_map_note' => 'Chọn một tỉnh/thành trên bản đồ để xem toàn bộ dự án nhà ở xã hội tại đó.',

            // --- cot phai: tin tuc noi bat -----------------------------------
            'projectaside_news_heading' => 'Tin tức nổi bật',
            'projectaside_news_more_text' => 'Xem thêm',

            // --- cot phai: khoi moi tu van -----------------------------------
            'projectaside_fit_heading' => 'Có dự án phù hợp với bạn?',
            'projectaside_fit_icon' => 'bulb-rays',
            'projectaside_fit_point_1' => 'Tư vấn chọn dự án theo nhu cầu',
            'projectaside_fit_point_2' => 'Kiểm tra điều kiện mua',
            'projectaside_fit_point_3' => 'Hỗ trợ chuẩn bị hồ sơ',
            'projectaside_fit_button' => 'ĐĂNG KÝ TƯ VẤN MIỄN PHÍ',
            'projectaside_fit_url' => 'kiem-tra-dieu-kien',

            // --- cot trai: banner tu van -------------------------------------
            'projectaside_banner_heading' => 'Cần tư vấn dự án phù hợp?',
            'projectaside_banner_point_1' => 'Tư vấn miễn phí',
            'projectaside_banner_point_2' => 'Kiểm tra điều kiện',
            'projectaside_banner_point_3' => 'Hỗ trợ hồ sơ',
            'projectaside_banner_button' => 'LIÊN HỆ NGAY',
        ];

        $them = 0;

        foreach ($chu as $khoa => $noiDung) {
            $co = DB::table('introduces')
                ->where('keyword', $khoa)->where('language_id', 1)->exists();

            if ($co) {
                continue;
            }

            DB::table('introduces')->insert([
                'keyword' => $khoa,
                'language_id' => 1,
                'content' => $noiDung,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $them++;
        }

        $this->command?->info("Da them {$them} o noi dung cho trang danh sach du an.");
    }
}
