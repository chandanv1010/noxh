<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Noi dung ban dau cho TRANG PHONG PHAP LY NOXH, chep tung chu tu ban thiet
 * ke noxh_image/plxh fix.jpg.
 *
 * Moi chu tren ban ve deu co mot o o day de trang Blade khong con chuoi nao
 * viet cung: tieu de, o chip, chu de, nhan tren nut, dai cam ket cuoi trang.
 *
 * Seeder CHI THEM o con trong: chay lai khong de len thu quan tri da sua.
 *
 *     php artisan db:seed --class=NoxhLegalPageSeeder --force
 */
class NoxhLegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $chu = [
            // --- dai dau trang -------------------------------------------------
            'legal_heading' => 'PHÒNG PHÁP LÝ NOXH',
            'legal_description' => 'Giải đáp pháp lý – Hỗ trợ hồ sơ – An tâm mua Nhà ở xã hội',
            'legal_intro' => 'Cung cấp thông tin pháp lý chính xác, cập nhật và dễ hiểu về Nhà ở xã hội. '
                . 'Giúp bạn hiểu đúng, làm đúng và bảo vệ quyền lợi của mình.',
            'legal_hero_icon' => 'scale',
            // Anh nen bau troi dung chung voi trang chi tiet du an.
            'legal_hero_bg' => '/uploads/noxh/nen-dau-trang.jpg',
            // Hai anh duoi day do tools/tach-anh-ban-ve.py cat tu chinh ban ve.
            'legal_hero_banner' => '/uploads/noxh/dai-phap-ly.png',

            'legal_chip_1_text' => "Kiến thức pháp lý\ndễ hiểu",
            'legal_chip_1_icon' => 'book',
            'legal_chip_2_text' => "Cập nhật chính sách\nmới nhất",
            'legal_chip_2_icon' => 'shield-check',
            'legal_chip_3_text' => "Hỗ trợ hồ sơ\nchi tiết",
            'legal_chip_3_icon' => 'doc-line',
            'legal_chip_4_text' => "Tư vấn bởi\nchuyên gia",
            'legal_chip_4_icon' => 'people',

            // --- the ho tro o goc phai dai dau trang ---------------------------
            'legal_help_heading' => 'CẦN HỖ TRỢ PHÁP LÝ?',
            'legal_help_image' => '/uploads/noxh/tu-van-vien.png',
            'legal_help_bullet_icon' => 'check-circle',
            'legal_help_button' => 'TƯ VẤN MIỄN PHÍ',
            'legal_help_button_icon' => 'sms',
            'legal_help_button_link' => '/cong-hoa/tu-van',
            'legal_help_phone_note' => '(Zalo / Call / SMS)',

            // --- khoi chu de ----------------------------------------------------
            'legal_topic_heading' => 'CHỦ ĐỀ PHÁP LÝ NOXH',

            'legal_topic_1_title' => 'Đối tượng & điều kiện',
            'legal_topic_1_description' => 'Ai được mua NOXH? Điều kiện về nhà ở, thu nhập, cư trú, hộ khẩu...',
            'legal_topic_1_icon' => 'people',
            'legal_topic_1_link' => '/kiem-tra-dieu-kien',

            'legal_topic_2_title' => 'Mua bán & chuyển nhượng',
            'legal_topic_2_description' => 'Quy định mua bán NOXH, thời hạn chuyển nhượng, tặng cho, thừa kế...',
            'legal_topic_2_icon' => 'home-door',
            'legal_topic_2_link' => '/phap-ly-noxh/van-ban',

            'legal_topic_3_title' => 'Hợp đồng & thanh toán',
            'legal_topic_3_description' => 'Hợp đồng mua bán, đặt cọc, tiến độ thanh toán, vay vốn ưu đãi...',
            'legal_topic_3_icon' => 'doc-pen',
            'legal_topic_3_link' => '/tai-chinh',

            'legal_topic_4_title' => 'Hồ sơ & thủ tục',
            'legal_topic_4_description' => 'Hồ sơ cần chuẩn bị, quy trình nộp hồ sơ, thẩm định và xét duyệt...',
            'legal_topic_4_icon' => 'clipboard-check',
            'legal_topic_4_link' => '/ho-so',

            'legal_topic_5_title' => 'Chính sách & văn bản',
            'legal_topic_5_description' => 'Văn bản pháp luật, nghị định, thông tư, công văn hướng dẫn mới nhất...',
            'legal_topic_5_icon' => 'scale',
            'legal_topic_5_link' => '/phap-ly-noxh/van-ban',

            // --- khoi bai viet ---------------------------------------------------
            'legal_post_heading' => 'BÀI VIẾT PHÁP LÝ MỚI NHẤT',
            'legal_post_all_text' => 'Xem tất cả',
            'legal_post_hot_text' => 'NỔI BẬT',
            'legal_post_view_text' => 'lượt xem',
            'legal_post_empty' => 'Chưa có bài viết nào.',

            // --- dai cam ket cuoi khoi trai ----------------------------------------
            'legal_cta_title' => 'Thông tin pháp lý được cập nhật liên tục và kiểm duyệt bởi đội ngũ Pháp lý NOXH.vn',
            'legal_cta_description' => 'Cam kết chính xác – Dễ hiểu – Ứng dụng thực tế',
            'legal_cta_icon' => 'shield-check',
            'legal_cta_button' => 'GỬI CÂU HỎI PHÁP LÝ',
            'legal_cta_link' => '/hoi-dap',

            // --- cot phai ----------------------------------------------------------
            'legal_doc_heading' => 'VĂN BẢN PHÁP LUẬT MỚI',
            'legal_doc_all_text' => 'Xem tất cả',
            'legal_doc_empty' => 'Chưa có văn bản nào.',
            'legal_doc_download_title' => 'Tải văn bản về',
            'legal_doc_effective_text' => 'Có hiệu lực từ {ngay}',

            // --- dai cam ket cuoi trang ---------------------------------------------
            'legal_trust_1_title' => 'Thông tin chính xác',
            'legal_trust_1_sub' => 'Kiểm duyệt bởi chuyên gia pháp lý',
            'legal_trust_1_icon' => 'trust-doc',
            'legal_trust_2_title' => 'Cập nhật liên tục',
            'legal_trust_2_sub' => 'Theo quy định mới nhất',
            'legal_trust_2_icon' => 'trust-live',
            'legal_trust_3_title' => 'Dễ hiểu – Dễ áp dụng',
            'legal_trust_3_sub' => 'Giải thích rõ ràng, ví dụ thực tế',
            'legal_trust_3_icon' => 'trust-chat',
            'legal_trust_4_title' => 'Bảo mật thông tin',
            'legal_trust_4_sub' => 'Cam kết bảo mật tuyệt đối cho khách hàng',
            'legal_trust_4_icon' => 'trust-lock',
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

        $this->command?->info("Da them {$them} o noi dung cho trang phong phap ly.");
    }
}
