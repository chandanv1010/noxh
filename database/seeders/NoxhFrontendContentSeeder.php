<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Noi dung mau cho frontend NOXH.vn.
 *
 * Chay lai nhieu lan khong sinh ban ghi trung: moi bang deu tim theo mot khoa
 * duy nhat truoc khi them.
 *
 * Seeder nay KHONG xoa gi ca - du lieu cu cua website GPS van con nguyen.
 */
class NoxhFrontendContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->napChu();
        $this->napCauHinh();
        $this->napChuyenGia();
        $this->napChuDauTu();
        $this->napDuAn();
        $this->napVanBan();
        $this->napHoSo();
        $this->napGoiVay();
        $this->napHoiDap();
        $this->napTinTuc();

        $this->command->info('Da nap xong noi dung mau cho frontend NOXH.');
    }

    // ---------------------------------------------------------------- chu
    private function napChu(): void
    {
        $chu = [
            'brand_tagline' => 'Rõ pháp lý – Đúng thông tin',
            'footer_description' => 'NOXH.vn là cổng thông tin nhà ở xã hội uy tín, minh bạch và cập nhật liên tục trên toàn quốc.',

            'hero_label' => 'Cổng thông tin',
            'hero_title' => 'Nhà ở xã hội',
            'hero_slogan' => 'Rõ pháp lý – Đúng thông tin – Chọn đúng nhà',
            'hero_description' => 'NOXH.vn giúp bạn tìm dự án phù hợp, kiểm tra điều kiện, chuẩn bị hồ sơ và được tư vấn bởi chuyên gia.',
            'hero_usp_1' => 'Thông tin minh bạch',
            'hero_usp_1_desc' => 'Cập nhật từ nguồn chính thống',
            'hero_usp_2' => 'Hiểu đúng pháp lý',
            'hero_usp_2_desc' => 'Hướng dẫn chi tiết, dễ hiểu',
            'hero_usp_3' => 'Tư vấn tận tâm',
            'hero_usp_3_desc' => 'Hỗ trợ đến khi an cư',

            'stat_1_value' => '120+',
            'stat_1_label' => 'Dự án trên toàn quốc',
            'stat_2_value' => '38',
            'stat_2_label' => 'Tỉnh / Thành phố',
            'stat_3_value' => '15250+',
            'stat_3_label' => 'Khách hàng được tư vấn',
            'stat_4_value' => '500+',
            'stat_4_label' => 'Câu hỏi đã được giải đáp',

            'check_title' => 'Bạn có đủ điều kiện mua nhà ở xã hội?',
            'check_description' => 'Trả lời 8 câu hỏi ngắn để nhận kết quả sơ bộ chỉ trong 1 phút.',
            'check_step_1' => 'Đối tượng',
            'check_step_1_desc' => 'Bạn thuộc nhóm đối tượng nào?',
            'check_step_2' => 'Tình trạng nhà ở',
            'check_step_2_desc' => 'Bạn đang có nhà ở hay chưa?',
            'check_step_3' => 'Thu nhập',
            'check_step_3_desc' => 'Thu nhập bình quân hàng tháng?',
            'check_step_4' => 'Cư trú',
            'check_step_4_desc' => 'Nơi cư trú hiện tại của bạn?',
            'check_step_5' => 'Kết quả',
            'check_step_5_desc' => 'Nhận kết quả sơ bộ tức thì',

            'useful_heading' => 'Thông tin hữu ích',
            'useful_1_title' => 'Pháp lý NOXH',
            'useful_1_desc' => 'Tìm hiểu điều kiện, đối tượng, quyền lợi và quy định pháp luật.',
            'useful_1_url' => 'phap-ly-noxh',
            'useful_2_title' => 'Hồ sơ cần chuẩn bị',
            'useful_2_desc' => 'Hướng dẫn chi tiết các loại giấy tờ, mẫu đơn và quy trình nộp hồ sơ.',
            'useful_2_url' => 'ho-so/can-chuan-bi',
            'useful_3_title' => 'Tính khả năng tài chính',
            'useful_3_desc' => 'Công cụ tính toàn vay, số tiền phải trả hàng tháng.',
            'useful_3_url' => 'tai-chinh/kha-nang-tai-chinh',
            'useful_4_title' => 'Vay ngân hàng',
            'useful_4_desc' => 'Thông tin các gói vay ưu đãi khi mua NOXH.',
            'useful_4_url' => 'tai-chinh/tinh-khoan-vay',
            'useful_5_title' => 'Hỏi đáp cộng đồng',
            'useful_5_desc' => 'Đặt câu hỏi và nhận giải đáp từ chuyên gia.',
            'useful_5_url' => 'hoi-dap',

            'project_heading' => 'Danh sách dự án nhà ở xã hội',
            'project_description' => "Cập nhật mới nhất các dự án NOXH trên toàn quốc.\nThông tin minh bạch – Pháp lý rõ ràng – Hỗ trợ tư vấn hồ sơ.",
            'legal_heading' => 'Phòng pháp lý NOXH',
            'legal_description' => 'Cung cấp thông tin pháp lý chính xác, cập nhật và dễ hiểu về Nhà ở xã hội.',
            'dossier_heading' => 'Hồ sơ mua nhà ở xã hội',
            'dossier_description' => 'Danh sách giấy tờ cần chuẩn bị theo từng nhóm đối tượng, kèm mẫu đơn tải về.',
            'finance_heading' => 'Công cụ tài chính',
            'finance_description' => 'Tính khoản vay và khả năng tài chính trước khi quyết định mua.',
            'qa_heading' => 'Hỏi đáp về nhà ở xã hội',
            'qa_description' => 'Đặt câu hỏi và nhận giải đáp từ chuyên gia pháp lý NOXH.',
            'news_heading' => 'Tin tức nhà ở xã hội',
            'news_description' => 'Chính sách, tiến độ dự án và hướng dẫn thủ tục mới nhất.',
        ];

        foreach ($chu as $khoa => $noiDung) {
            DB::table('introduces')->updateOrInsert(
                ['keyword' => $khoa, 'language_id' => 1],
                ['content' => $noiDung, 'user_id' => 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function napCauHinh(): void
    {
        $cauHinh = [
            'homepage_company' => 'NOXH.vn',
            'homepage_copyright' => '© ' . date('Y') . ' NOXH.vn. All rights reserved.',
            'seo_meta_title' => 'NOXH.vn - Cổng thông tin nhà ở xã hội toàn quốc',
            'seo_meta_description' => 'Tra cứu dự án nhà ở xã hội, kiểm tra điều kiện mua, chuẩn bị hồ sơ và nhận tư vấn từ chuyên gia pháp lý.',
            'contact_hotline' => '0989 591 616 | 0942 141 686',
            'contact_email' => 'info@noxh.vn',
            'contact_address' => 'Số 12, đường Lương Ngọc Quyến, TP. Thái Nguyên',
            'contact_working_hours' => '8:00 – 21:00 (T2 – CN)',
        ];

        foreach ($cauHinh as $khoa => $noiDung) {
            DB::table('systems')->updateOrInsert(
                ['keyword' => $khoa, 'language_id' => 1],
                ['content' => $noiDung, 'user_id' => 1, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    private function napChuyenGia(): void
    {
        if (DB::table('experts')->where('name', 'Công Hòa')->exists()) {
            return;
        }

        DB::table('experts')->insert([
            'name' => 'Công Hòa',
            'title' => 'Chuyên gia tư vấn Nhà ở xã hội',
            'phone' => '0989 591 616',
            'zalo' => '0989591616',
            'email' => 'conghoa@noxh.vn',
            'description' => 'Đội ngũ chuyên viên pháp lý của NOXH.vn sẵn sàng hỗ trợ bạn 24/7.',
            'commitments' => "Tư vấn pháp lý NOXH\nKiểm tra điều kiện – hồ sơ\nHỗ trợ hồ sơ trọn gói\nĐồng hành đến khi có nhà",
            'is_default' => 1,
            'publish' => 2,
            'order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function napChuDauTu(): void
    {
        foreach ([
            ['Công ty CP Đầu tư và Phát triển TNG', 'TNG', '0208 3xxx xxxx', 'info@tng.vn'],
            ['Tổng công ty Đầu tư phát triển nhà và đô thị HUD', 'HUD', '024 3xxx xxxx', 'info@hud.vn'],
        ] as $i => $cdt) {
            if (DB::table('investors')->where('name', $cdt[0])->exists()) {
                continue;
            }

            DB::table('investors')->insert([
                'name' => $cdt[0],
                'short_name' => $cdt[1],
                'hotline' => $cdt[2],
                'email' => $cdt[3],
                'publish' => 2,
                'order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function napDuAn(): void
    {
        $danhMuc = DB::table('product_catalogues')->orderBy('id')->value('id');
        $cdt = DB::table('investors')->orderBy('id')->value('id');

        // Ma tinh lay tu bang provinces de bo loc theo tinh chay dung.
        $tinh = DB::table('provinces')->pluck('code', 'name')->toArray();
        $ma = function (string $ten) use ($tinh) {
            foreach ($tinh as $t => $c) {
                if (str_contains($t, $ten)) {
                    return $c;
                }
            }
            return null;
        };

        $duAn = [
            ['NOXH Túc Duyên', 'noxh-tuc-duyen', 'Thái Nguyên', 'building', 19.55, 23.99, 32, 70, 1042, 8.12, '04 khối chung cư cao 9 tầng', 'Quý IV/2026', 1],
            ['NOXH Hồng Tiến', 'noxh-hong-tien', 'Thái Nguyên', 'upcoming', 18.6, 22.5, 35, 68, 800, 5.4, '03 khối chung cư', 'Quý II/2027', 1],
            ['NOXH Evergreen Bắc Giang', 'noxh-evergreen-bac-giang', 'Bắc Giang', 'receiving', 16.8, 21.3, 32, 65, 1200, 9.8, '05 khối chung cư', 'Quý I/2027', 1],
            ['NOXH IEC Residences', 'noxh-iec-residences', 'Bình Định', 'building', 17.2, 22.1, 38, 68, 1500, 11.2, '06 khối chung cư', 'Quý III/2027', 0],
            ['NOXH Phúc Thịnh', 'noxh-phuc-thinh', 'Hà Nội', 'upcoming', 20.0, 24.5, 45, 70, 2000, 14.5, '08 khối chung cư', 'Quý IV/2027', 0],
            ['NOXH Sông Công', 'noxh-song-cong', 'Thái Nguyên', 'handed', 16.5, 18.9, 30, 60, 640, 4.2, '02 khối chung cư', 'Đã bàn giao', 0],
        ];

        foreach ($duAn as $i => $d) {
            if (DB::table('product_language')->where('canonical', $d[1])->exists()) {
                continue;
            }

            $id = DB::table('products')->insertGetId([
                'product_catalogue_id' => $danhMuc,
                'investor_id' => $cdt,
                'province_code' => $ma($d[2]),
                'status' => $d[3],
                'price_from' => $d[4],
                'price_to' => $d[5],
                'area_from' => $d[6],
                'area_to' => $d[7],
                'total_units' => $d[8],
                'total_land_area' => $d[9],
                'scale_description' => $d[10],
                'timeline_label' => $d[11],
                'is_featured' => $d[12],
                'ownership_type' => 'Sở hữu 50 năm',
                'apartment_types' => '1PN, 2PN, 3PN',
                'address' => 'Phường ' . Str::title(str_replace('noxh-', '', $d[1])) . ', ' . $d[2],
                'publish' => 2,
                'follow' => 2,
                'order' => $i,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('product_language')->insert([
                'product_id' => $id,
                'language_id' => 1,
                'name' => $d[0],
                'canonical' => $d[1],
                'description' => 'Dự án nhà ở xã hội ' . $d[0] . ' tại ' . $d[2] . ', quy mô ' . number_format($d[8]) . ' căn hộ, vị trí thuận lợi, hạ tầng đồng bộ.',
                'content' => '<p>Dự án <strong>' . $d[0] . '</strong> nằm tại vị trí trung tâm ' . $d[2] . ', được đầu tư đồng bộ hạ tầng kỹ thuật, hạ tầng xã hội vượt thiết kế hiện đại, không gian sống xanh, đáp ứng nhu cầu an cư cho người thu nhập thấp.</p>',
                'meta_title' => $d[0] . ' - Nhà ở xã hội ' . $d[2],
                'meta_description' => 'Thông tin dự án ' . $d[0] . ': giá bán, diện tích, tiến độ, pháp lý và điều kiện mua.',
            ]);

            DB::table('product_catalogue_product')->insert([
                'product_id' => $id,
                'product_catalogue_id' => $danhMuc,
            ]);

            // Tien do, ho so phap ly va cau hoi thuong gap cho du an dau tien.
            if ($i === 0) {
                foreach ([
                    ['Khởi công xây dựng', 'Quý II/2024', 'done'],
                    ['Hoàn thành phần móng', 'Quý IV/2024', 'done'],
                    ['Thi công phần thân', 'Quý II/2025', 'doing'],
                    ['Dự kiến bàn giao', 'Quý IV/2026', 'pending'],
                ] as $k => $moc) {
                    DB::table('project_milestones')->insert([
                        'product_id' => $id, 'title' => $moc[0], 'date_label' => $moc[1],
                        'status' => $moc[2], 'order' => $k,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }

                foreach ([
                    ['Quyết định chủ trương đầu tư', '123/QĐ-UBND', '2024-03-15'],
                    ['Giấy phép xây dựng', '456/GPXD', '2024-04-20'],
                    ['Hồ sơ thiết kế cơ sở', '789/TKCS', '2024-04-25'],
                    ['Thông báo đủ điều kiện mở bán', '111/TB-SXD', '2024-06-10'],
                ] as $k => $hs) {
                    DB::table('project_documents')->insert([
                        'product_id' => $id, 'title' => $hs[0], 'doc_number' => $hs[1],
                        'issued_date' => $hs[2], 'publish' => 2, 'order' => $k,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }

                foreach ([
                    ['Ai được mua NOXH tại dự án này?', 'Người thuộc nhóm đối tượng được mua NOXH theo quy định và đáp ứng điều kiện về nhà ở, thu nhập, cư trú.'],
                    ['Giá bán căn hộ dự kiến bao nhiêu?', 'Từ 19,55 đến 23,99 triệu đồng/m², chưa bao gồm phí bảo trì và các khoản phí khác.'],
                    ['Khi nào dự án mở bán?', 'Dự án đã có thông báo đủ điều kiện mở bán, vui lòng liên hệ hotline để biết đợt mở bán gần nhất.'],
                    ['Có được chuyển nhượng sau khi mua không?', 'Theo quy định, NOXH chỉ được chuyển nhượng sau tối thiểu 5 năm kể từ khi thanh toán hết tiền mua.'],
                ] as $k => $ch) {
                    DB::table('project_faqs')->insert([
                        'product_id' => $id, 'question' => $ch[0], 'answer' => $ch[1],
                        'publish' => 2, 'order' => $k,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    private function napVanBan(): void
    {
        foreach ([
            ['Luật Nhà ở số 27/2023/QH15', '27/2023/QH15', 'luat', '2024-08-01', 'Quốc hội'],
            ['Nghị định 100/2024/NĐ-CP', '100/2024/NĐ-CP', 'nghi_dinh', '2024-07-26', 'Chính phủ'],
            ['Thông tư 05/2024/TT-BXD', '05/2024/TT-BXD', 'thong_tu', '2024-07-15', 'Bộ Xây dựng'],
            ['Công văn 2145/BXD-QLN', '2145/BXD-QLN', 'cong_van', '2024-07-10', 'Bộ Xây dựng'],
        ] as $i => $vb) {
            if (DB::table('legal_documents')->where('doc_number', $vb[1])->exists()) {
                continue;
            }

            DB::table('legal_documents')->insert([
                'title' => $vb[0],
                'doc_number' => $vb[1],
                'doc_type' => $vb[2],
                'effective_date' => $vb[3],
                'issued_date' => $vb[3],
                'issuer' => $vb[4],
                'summary' => 'Quy định chi tiết một số điều của Luật Nhà ở về phát triển và quản lý nhà ở xã hội.',
                'is_featured' => $i < 2 ? 1 : 0,
                'publish' => 2,
                'order' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function napHoSo(): void
    {
        $bo = [
            ['Hồ sơ cho công nhân khu công nghiệp', 'ho-so-cong-nhan', 'Công nhân khu công nghiệp'],
            ['Hồ sơ cho cán bộ, công chức, viên chức', 'ho-so-can-bo', 'Cán bộ, công chức, viên chức'],
        ];

        $giayTo = [
            ['Đơn đăng ký mua nhà ở xã hội', 'UBND phường/xã', 2, 1, 'Theo mẫu số 01 ban hành kèm Nghị định 100/2024/NĐ-CP.'],
            ['Giấy xác nhận về nhà ở, thu nhập', 'Cơ quan/đơn vị công tác', 2, 1, 'Xác nhận chưa có nhà ở và mức thu nhập hàng tháng.'],
            ['Giấy tờ chứng minh nhân thân, hộ khẩu', 'Công an xã/phường', 2, 1, 'CCCD và giấy xác nhận cư trú.'],
            ['Giấy tờ chứng minh đối tượng', 'Cơ quan có thẩm quyền', 2, 1, 'Tùy theo nhóm đối tượng được hưởng chính sách.'],
            ['Các giấy tờ khác theo hướng dẫn', 'Chủ đầu tư', 1, 0, 'Chủ đầu tư sẽ hướng dẫn cụ thể theo từng dự án.'],
        ];

        foreach ($bo as $i => $b) {
            $id = DB::table('dossier_sets')->where('canonical', $b[1])->value('id');

            if (!$id) {
                $id = DB::table('dossier_sets')->insertGetId([
                    'name' => $b[0], 'canonical' => $b[1], 'subject_group' => $b[2],
                    'description' => 'Danh sách giấy tờ cần chuẩn bị khi nộp hồ sơ mua nhà ở xã hội.',
                    'publish' => 2, 'order' => $i,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            foreach ($giayTo as $k => $gt) {
                if (DB::table('dossier_items')->where('dossier_set_id', $id)->where('title', $gt[0])->exists()) {
                    continue;
                }

                DB::table('dossier_items')->insert([
                    'dossier_set_id' => $id, 'title' => $gt[0], 'issued_by' => $gt[1],
                    'copies' => $gt[2], 'is_required' => $gt[3], 'description' => $gt[4],
                    'publish' => 2, 'order' => $k,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    private function napGoiVay(): void
    {
        foreach ([
            ['Ngân hàng Chính sách xã hội', 'Gói vay NOXH ưu đãi', 4.8, 60, 6.6, 80, 25],
            ['Agribank', 'Gói vay nhà ở xã hội', 5.5, 36, 9.0, 70, 20],
            ['BIDV', 'Gói 120.000 tỷ', 6.5, 60, 9.5, 70, 20],
        ] as $i => $g) {
            if (DB::table('loan_packages')->where('bank_name', $g[0])->where('package_name', $g[1])->exists()) {
                continue;
            }

            DB::table('loan_packages')->insert([
                'bank_name' => $g[0], 'package_name' => $g[1],
                'preferential_rate' => $g[2], 'preferential_months' => $g[3],
                'standard_rate' => $g[4], 'max_loan_ratio' => $g[5], 'max_term_years' => $g[6],
                'conditions' => 'Áp dụng cho khách hàng thuộc đối tượng được mua nhà ở xã hội theo quy định.',
                'is_featured' => $i === 0 ? 1 : 0,
                'publish' => 2, 'order' => $i,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function napHoiDap(): void
    {
        $expert = DB::table('experts')->orderBy('id')->value('id');

        foreach ([
            ['Tôi có thể mua NOXH nếu chưa có sổ hộ khẩu tại địa phương?', 'Nguyễn Văn An', 'Theo Luật Nhà ở 2023, điều kiện về cư trú đã được nới lỏng. Bạn chỉ cần đang làm việc tại tỉnh/thành phố nơi có dự án và có giấy xác nhận của đơn vị công tác, không bắt buộc phải có hộ khẩu thường trú.', 3200],
            ['Thu nhập 15 triệu/tháng có đủ điều kiện mua NOXH không?', 'Trần Thị Mai', 'Nghị định 100/2024/NĐ-CP quy định mức thu nhập không quá 15 triệu đồng/tháng đối với người độc thân và không quá 30 triệu đồng/tháng đối với hộ gia đình. Với mức 15 triệu, bạn vẫn thuộc diện được mua.', 2100],
            ['Mua NOXH rồi có được bán lại không?', 'Lê Văn Hùng', 'Nhà ở xã hội chỉ được bán lại sau tối thiểu 5 năm kể từ thời điểm thanh toán hết tiền mua. Trước thời hạn này, bạn chỉ được bán lại cho chủ đầu tư hoặc đối tượng thuộc diện được mua NOXH.', 1800],
            ['Hồ sơ mua NOXH gồm những gì?', 'Phạm Thu Hà', 'Hồ sơ gồm: đơn đăng ký mua, giấy xác nhận về nhà ở và thu nhập, giấy tờ nhân thân, giấy tờ chứng minh đối tượng. Chi tiết xem tại mục Hồ sơ trên website.', 1500],
        ] as $i => $q) {
            if (DB::table('qa_questions')->where('title', $q[0])->exists()) {
                continue;
            }

            $id = DB::table('qa_questions')->insertGetId([
                'title' => $q[0], 'asker_name' => $q[1], 'view_count' => $q[3],
                'is_featured' => $i < 3 ? 1 : 0, 'status' => 'answered', 'publish' => 2,
                'created_at' => now()->subDays($i + 1), 'updated_at' => now(),
            ]);

            DB::table('qa_answers')->insert([
                'qa_question_id' => $id, 'content' => '<p>' . $q[2] . '</p>',
                'expert_id' => $expert, 'is_official' => 1, 'publish' => 2,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function napTinTuc(): void
    {
        $chuyenMuc = DB::table('post_catalogues')->orderBy('id')->value('id');

        foreach ([
            ['Chính thức: 4 nhóm đối tượng mới được mua NOXH từ 07/04/2026', 'bon-nhom-doi-tuong-moi-duoc-mua-noxh'],
            ['Lãi suất vay mua NOXH tháng 5/2026 mới nhất các ngân hàng', 'lai-suat-vay-mua-noxh-thang-5-2026'],
            ['Hướng dẫn chi tiết thủ tục hồ sơ mua nhà ở xã hội năm 2026', 'huong-dan-thu-tuc-ho-so-mua-noxh-2026'],
            ['Điều kiện mới nhất để được mua nhà ở xã hội', 'dieu-kien-moi-nhat-de-duoc-mua-noxh'],
        ] as $i => $b) {
            if (DB::table('post_language')->where('canonical', $b[1])->exists()) {
                continue;
            }

            $id = DB::table('posts')->insertGetId([
                'post_catalogue_id' => $chuyenMuc,
                'publish' => 2, 'follow' => 2, 'order' => $i, 'user_id' => 1,
                'created_at' => now()->subDays($i), 'updated_at' => now(),
            ]);

            DB::table('post_language')->insert([
                'post_id' => $id, 'language_id' => 1,
                'name' => $b[0], 'canonical' => $b[1],
                'description' => 'Cập nhật theo Luật Nhà ở 2023 và Nghị định 100/2024/NĐ-CP, áp dụng trên toàn quốc.',
                'content' => '<p>Nội dung chi tiết của bài viết <strong>' . $b[0] . '</strong> sẽ được cập nhật trong trang quản trị.</p>',
                'meta_title' => $b[0],
                'meta_description' => 'Cập nhật theo Luật Nhà ở 2023 và Nghị định 100/2024/NĐ-CP.',
            ]);

            if (DB::getSchemaBuilder()->hasTable('post_catalogue_post')) {
                DB::table('post_catalogue_post')->insert([
                    'post_id' => $id, 'post_catalogue_id' => $chuyenMuc,
                ]);
            }
        }
    }
}
