<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Noi dung ban dau cho cac o quan tri cua TRANG CHI TIET DU AN, dung lai
 * theo ban thiet ke noxh_image/product-detail-fix.jpg.
 *
 * Moi chu tren ban ve deu phai co mot o o day: tieu de khoi, nhan o thong
 * so, chu tren nut, dong cam ket... De trang Blade khong con mot chuoi nao
 * viet cung.
 *
 * Seeder CHI THEM o con trong: chay lai khong de len thu quan tri da sua.
 *
 *     php artisan db:seed --class=NoxhProjectDetailSeeder --force
 */
class NoxhProjectDetailSeeder extends Seeder
{
    public function run(): void
    {
        $chu = [
            // --- dai dau trang -----------------------------------------------
            'projectdetail_hero_slogan' => "An cư hôm nay\nKiến tạo tương lai",
            // Anh nen bau troi ve san, de quan tri thay bang anh that sau.
            'projectdetail_hero_bg' => '/uploads/noxh/nen-dau-trang.jpg',
            'projectdetail_video_text' => 'Xem video dự án',
            'projectdetail_gallery_more' => '+ {so} ảnh',

            // --- the gia ------------------------------------------------------
            'projectdetail_price_label' => 'Giá bán dự kiến',
            'projectdetail_price_note' => '(Chưa bao gồm phí khác)',
            'projectdetail_price_empty' => 'Đang cập nhật',
            'projectdetail_price_button' => 'ĐĂNG KÝ TƯ VẤN NGAY',
            'projectdetail_trust_1' => 'Bảo mật thông tin',
            'projectdetail_trust_2' => 'Miễn phí tư vấn',
            'projectdetail_trust_3' => 'Hỗ trợ 24/7',
            'projectdetail_trust_icon' => 'shield-check',

            // Bon o thong so: chi nhan, con so lay tu du an.
            'projectdetail_spec_1_label' => 'Quy mô',
            'projectdetail_spec_1_icon' => 'city',
            'projectdetail_spec_2_label' => 'Số căn hộ',
            'projectdetail_spec_2_icon' => 'units',
            'projectdetail_spec_3_label' => 'Loại hình',
            'projectdetail_spec_3_icon' => 'area',
            'projectdetail_spec_4_label' => 'Bàn giao',
            'projectdetail_spec_4_icon' => 'schedule',

            // Bon o diem nhan mac dinh - du an khai rieng thi lay cua du an.
            'projectdetail_point_1_title' => 'Vị trí',
            'projectdetail_point_1_sub' => 'trung tâm',
            'projectdetail_point_1_icon' => 'map-pins',
            'projectdetail_point_2_title' => 'Hạ tầng',
            'projectdetail_point_2_sub' => 'đồng bộ',
            'projectdetail_point_2_icon' => 'city',
            'projectdetail_point_3_title' => 'Tiện ích',
            'projectdetail_point_3_sub' => 'đa dạng',
            'projectdetail_point_3_icon' => 'bulb-rays',
            'projectdetail_point_4_title' => 'Pháp lý',
            'projectdetail_point_4_sub' => 'rõ ràng',
            'projectdetail_point_4_icon' => 'verified',

            // --- cac khoi noi dung ---------------------------------------------
            // Muoi tab dung thu tu ban ve. Hinh tren tab la hinh boc ra tu
            // ban ve, khai o day de quan tri doi duoc.
            'projectdetail_overview_heading' => 'TỔNG QUAN DỰ ÁN',
            'projectdetail_overview_tab' => 'Tổng quan',
            'projectdetail_overview_icon' => 'tab-overview',
            'projectdetail_overview_photo_text' => 'Xem ảnh thực tế',

            'projectdetail_location_heading' => 'VỊ TRÍ DỰ ÁN',
            'projectdetail_location_tab' => 'Vị trí',
            'projectdetail_location_icon' => 'tab-location',
            'projectdetail_location_button' => 'Xem trên Google Maps',

            'projectdetail_units_heading' => 'MẶT BẰNG DỰ ÁN',
            'projectdetail_units_tab' => 'Mặt bằng',
            'projectdetail_units_icon' => 'tab-plan',
            'projectdetail_units_block_heading' => 'CÁC LOẠI CĂN HỘ',
            'projectdetail_units_all_text' => 'Xem tất cả',
            'projectdetail_units_detail_text' => 'XEM CHI TIẾT',
            'projectdetail_units_area_label' => 'Diện tích:',
            'projectdetail_units_price_label' => 'Giá dự kiến:',

            'projectdetail_amenity_heading' => 'TIỆN ÍCH DỰ ÁN',
            'projectdetail_amenity_tab' => 'Tiện ích',
            'projectdetail_amenity_icon' => 'tab-amenity',

            'projectdetail_price_heading' => 'GIÁ BÁN DỰ KIẾN',
            'projectdetail_price_tab' => 'Giá bán',
            'projectdetail_price_icon' => 'tab-price',
            'projectdetail_price_col_name' => 'Loại căn hộ',
            'projectdetail_price_col_area' => 'Diện tích',
            'projectdetail_price_col_price' => 'Giá dự kiến',

            'projectdetail_progress_heading' => 'TIẾN ĐỘ DỰ ÁN',
            'projectdetail_progress_tab' => 'Tiến độ',
            'projectdetail_progress_icon' => 'tab-progress',
            'projectdetail_progress_button' => 'Xem cập nhật tiến độ',
            'projectdetail_progress_modal_heading' => 'TOÀN BỘ TIẾN ĐỘ DỰ ÁN',

            'projectdetail_legal_heading' => 'PHÁP LÝ DỰ ÁN',
            'projectdetail_legal_tab' => 'Pháp lý',
            'projectdetail_legal_icon' => 'tab-legal',

            'projectdetail_gallery_heading' => 'HÌNH ẢNH - VIDEO DỰ ÁN',
            'projectdetail_gallery_tab' => 'Hình ảnh - Video',
            'projectdetail_gallery_icon' => 'tab-gallery',
            'projectdetail_gallery_video_text' => 'Xem video dự án',

            'projectdetail_doc_heading' => 'TÀI LIỆU DỰ ÁN',
            'projectdetail_doc_tab' => 'Tài liệu',
            'projectdetail_doc_icon' => 'tab-doc',

            'projectdetail_faq_heading' => 'CÂU HỎI THƯỜNG GẶP',
            'projectdetail_faq_tab' => 'Hỏi đáp',
            'projectdetail_faq_icon' => 'tab-faq',

            'projectdetail_content_heading' => 'GIỚI THIỆU CHI TIẾT',
            'projectdetail_empty_text' => 'Đang cập nhật',

            // Nhan tung dong cua bang Tong quan.
            'projectdetail_row_name' => 'Tên dự án',
            'projectdetail_row_place' => 'Vị trí',
            'projectdetail_row_investor' => 'Chủ đầu tư',
            'projectdetail_row_land' => 'Tổng diện tích',
            'projectdetail_row_scale' => 'Quy mô',
            'projectdetail_row_units' => 'Tổng số căn',
            'projectdetail_row_types' => 'Loại hình căn hộ',
            'projectdetail_row_area' => 'Diện tích căn hộ',
            'projectdetail_row_price' => 'Giá bán dự kiến',
            'projectdetail_row_ownership' => 'Hình thức sở hữu',
            'projectdetail_row_start' => 'Khởi công',
            'projectdetail_row_handover' => 'Dự kiến bàn giao',
            'projectdetail_row_status' => 'Trạng thái',

            'projectdetail_similar_price_prefix' => 'Từ',
            'projectdetail_similar_heading' => 'DỰ ÁN TƯƠNG TỰ TẠI {tinh}',
            'projectdetail_similar_all_text' => 'Xem tất cả',

            // --- cot phai: tu van nhanh ------------------------------------------
            'projectlead_quick_heading' => 'Tư vấn nhanh cùng NOXH.vn',
            'projectlead_quick_note' => 'Đội ngũ chuyên viên luôn sẵn sàng hỗ trợ bạn 24/7',
            'projectlead_quick_channel' => '(Zalo / Call / SMS)',
            'projectlead_quick_icon' => 'headset',

            // --- cot phai: danh sach tu van ---------------------------------------
            'projectlead_staff_heading' => 'DANH SÁCH TƯ VẤN HỖ TRỢ',
            'projectlead_staff_modal_heading' => 'TƯ VẤN VIÊN PHỤ TRÁCH DỰ ÁN',
            'projectlead_staff_note' => 'Đội ngũ tư vấn hỗ trợ khách hàng tại {tinh}',
            'projectlead_staff_verify' => '(Thông tin được xác minh bởi NOXH.vn)',
            'projectlead_staff_role' => 'Tư vấn nhà ở NOXH',
            'projectlead_staff_area' => 'Khu vực: {tinh}',
            'projectlead_staff_button' => 'Liên hệ',
            'projectlead_staff_more' => 'Xem thêm tư vấn viên khác',
            'projectlead_staff_empty' => 'Dự án đang được phân công chuyên viên phụ trách. Bạn để lại số điện thoại ở form bên dưới, NOXH.vn sẽ gọi lại ngay.',

            // --- cot phai: form dang ky -------------------------------------------
            'projectlead_form_heading' => 'ĐĂNG KÝ NHẬN THÔNG TIN DỰ ÁN',
            'projectlead_form_note' => 'Để được tư vấn chi tiết, nhận mặt bằng, bảng giá và thông báo mới nhất.',
            'projectlead_form_name' => 'Họ và tên *',
            'projectlead_form_phone' => 'Số điện thoại *',
            'projectlead_form_interest' => 'Nhu cầu quan tâm',
            'projectlead_form_interest_options' => "Căn 1 phòng ngủ\nCăn 2 phòng ngủ\nCăn 3 phòng ngủ\nChưa xác định",
            'projectlead_form_timeline' => 'Thời gian dự kiến mua',
            'projectlead_form_timeline_options' => "Trong 3 tháng tới\nTrong 6 tháng tới\nTrong 1 năm tới\nChưa xác định",
            'projectlead_form_button' => 'ĐĂNG KÝ NGAY',
            'projectlead_form_privacy' => 'Thông tin của bạn được bảo mật tuyệt đối.',
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

        $this->command?->info("Da them {$them} o noi dung cho trang chi tiet du an.");
    }
}
