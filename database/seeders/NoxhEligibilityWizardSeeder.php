<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Noi dung ban dau cho BO KIEM TRA DIEU KIEN dang wizard, chep tung chu tu
 * ban thiet ke noxh_image/w-1.jpg.
 *
 * Gom bon phan:
 *   1. Cac o chu khung cua trang (bang introduces, nhom "wizard").
 *   2. Ten NGAN cua tung buoc + dong luu y duoi luoi dap an, va thu tu buoc:
 *      ban ve dat "Doi tuong" o buoc 1, bang cu de o buoc 6.
 *   3. Bo 12 dap an cua cau "Doi tuong" kem hinh va mau hinh tung o.
 *   4. Hinh + mau cho dap an cua cac cau con lai, de luoi khong bi trong.
 *
 * Seeder CHI THEM o con trong / con chua co, tru phan thu tu buoc va bo dap
 * an cua cau "Doi tuong" - hai thu do phai dung dung ban ve nen ghi de.
 *
 *     php artisan db:seed --class=NoxhEligibilityWizardSeeder --force
 */
class NoxhEligibilityWizardSeeder extends Seeder
{
    /**
     * 12 nhom doi tuong o buoc 1, dung thu tu tren ban ve (doc theo hang).
     *
     * [gia tri luu, nhan, ghi chu nho, hinh, mau hinh]
     *
     * LUU Y: danh muc nay chep tu ban ve de co du lieu mau. Doi tuong duoc
     * mua nha o xa hoi thay doi theo nghi dinh, nen truoc khi chay that phai
     * doi chieu lai voi van ban dang co hieu luc - sua ngay trong man hinh
     * "Dap an dieu kien", khong phai sua file nay.
     */
    private const DOI_TUONG = [
        ['cach-mang', 'Người có công với cách mạng', '', 'medal', 'rose'],
        ['ho-ngheo-nong-thon', 'Hộ nghèo, cận nghèo (nông thôn)', '', 'cottage', 'sky'],
        ['ho-ngheo-thien-tai', 'Hộ nghèo, cận nghèo', '(khu vực thường xuyên bị thiên tai, biến đổi khí hậu)', 'storm', 'green'],
        ['ho-ngheo-do-thi', 'Hộ nghèo, cận nghèo (đô thị)', '', 'city', 'violet'],
        ['thu-nhap-thap', 'Người thu nhập thấp tại khu vực đô thị', '', 'wallet', 'amber'],
        ['cong-nhan', 'Công nhân, người lao động', '', 'factory', 'sky'],
        ['luc-luong-vu-trang', 'Sĩ quan, quân nhân chuyên nghiệp, hạ sĩ quan, lực lượng vũ trang, công an', '', 'military', 'green'],
        ['can-bo', 'Cán bộ, công chức, viên chức', '', 'badge', 'sky'],
        ['hoc-sinh-sinh-vien', 'Học sinh, sinh viên', '', 'school', 'purple'],
        ['doanh-nghiep', 'Doanh nghiệp, hợp tác xã, liên hiệp HTX', '', 'handshake', 'teal'],
        ['hai-con', 'Người có từ 02 con trở lên', '(có hiệu lực từ 01/7/2026)', 'family', 'rose'],
        ['chua-xac-dinh', 'Chưa xác định', 'Tôi cần tư vấn thêm', 'dots', 'slate'],
    ];

    /**
     * Ket luan va diem cua tung nhom doi tuong.
     *
     * Chi nhom "chua xac dinh" la can hoi them; cac nhom con lai deu nam
     * trong danh muc duoc mua nen deu dat.
     */
    private const KET_LUAN_DOI_TUONG = [
        'chua-xac-dinh' => ['unclear', 0],
    ];

    /**
     * Thu tu buoc + ten buoc + dong luu y, tra theo cau hoi.
     *
     * Khoa la mot doan chu co trong cau hoi - tra theo id thi may khac chay
     * ra so khac. Vai cau doi loi van theo ban ve moi nen khoa cho phep viet
     * NHIEU doan, ngan cach bang dau |, khop doan nao cung duoc.
     *
     * [doan chu nhan dang cau hoi] => [thu tu, ten buoc, dong luu y]
     */
    private const BUOC = [
        'nhóm đối tượng' => [0, 'Đối tượng', 'Danh mục nhóm đối tượng được xây dựng theo quy định hiện hành. Vui lòng chọn đúng nhóm phù hợp.'],
        'thu nhập bình quân|thu nhập hàng tháng' => [1, 'Thu nhập', 'Thu nhập được xem xét theo bình quân 12 tháng liền kề, theo quy định hiện hành.'],
        'hỗ trợ nhà ở dưới mọi hình thức|chính sách hỗ trợ về nhà ở' => [2, 'Chính sách', 'Lưu ý: Thông tin này giúp NOXH.vn xác định sơ bộ khả năng đáp ứng điều kiện mua nhà ở xã hội theo quy định hiện hành.'],
        'đã có nhà ở thuộc sở hữu|có nhà ở thuộc sở hữu không' => [3, 'Nhà ở', 'Vui lòng cung cấp địa điểm nhà ở hiện tại và nơi làm việc để chúng tôi kiểm tra điều kiện theo quy định.'],
        'kết hôn|Nhận kết quả kiểm tra' => [4, 'Kết quả', ''],
    ];

    /**
     * Ba tinh huong cua cau hoi thu nhap (ban ve noxh_image/w-2.jpg).
     *
     * [ten tam, dong ghi chu, tranh, hinh du phong, mau nen, nguong trieu/thang]
     *
     * LUU Y: ba muc 25 / 35 / 50 trieu chep tu ban ve de co du lieu mau.
     * Nguong thu nhap thay doi theo nghi dinh - sua trong man hinh quan tri,
     * khong sua file nay.
     */
    private const THU_NHAP = [
        ['doc-than', 'Độc thân', '(chưa kết hôn)', 'th-doc-than.png', 'user', 'sky', 25],
        ['nuoi-con', 'Độc thân nuôi con nhỏ', '(có con dưới 18 tuổi)', 'th-nuoi-con.png', 'family', 'amber', 35],
        ['ket-hon', 'Đã kết hôn', '(tổng thu nhập hai vợ chồng)', 'th-ket-hon.png', 'group', 'rose', 50],
    ];

    /** Hinh mac dinh cho dap an cua cac cau con lai, tra theo gia tri luu. */
    private const HINH_DAP_AN = [
        'no' => ['check-circle', 'green'],
        'yes' => ['warning', 'amber'],
        '1' => ['user', 'sky'],
        '2' => ['group', 'sky'],
        '3' => ['users', 'violet'],
        '4' => ['users', 'teal'],
        '5+' => ['family', 'rose'],
        'lt15' => ['piggy', 'green'],
        '15-25' => ['money', 'sky'],
        '25-35' => ['coins', 'amber'],
        '35-50' => ['chart', 'violet'],
        'gt50' => ['warning', 'rose'],
        'single' => ['user', 'sky'],
        'married' => ['family', 'rose'],
    ];

    public function run(): void
    {
        $this->napChu();
        $this->napBuoc();
        $this->napDoiTuong();
        $this->napThuNhap();
        $this->napChinhSach();
        $this->napNhaO();
        $this->napNhapTin();
        $this->anBuocThua();
        $this->napTamCotPhai();
        $this->napTieuChi();
        $this->napHinhDapAn();
    }

    // -------------------------------------------------------------------------

    private function napChu(): void
    {
        $chu = [
            // --- trang mo dau (start-fix.jpg) ---
            'start_bg' => '/uploads/noxh/kiem-tra-nen.jpg',
            'start_image' => '/uploads/noxh/kt-bang-kep.png',
            'start_heading' => 'Kiểm tra điều kiện mua',
            'start_heading_blue' => 'Nhà ở xã hội',
            'start_description' => 'Trả lời một số câu hỏi để kiểm tra sơ bộ khả năng đáp ứng điều kiện mua NOXH theo quy định hiện hành.',
            'start_time_text' => 'Thời gian thực hiện khoảng',
            'start_time_strong' => '3 – 5 phút',
            'start_time_icon' => 'clock-line',
            'start_privacy_image' => '/uploads/noxh/kt-khien-khoa.png',
            'start_privacy_heading' => 'Thông tin của bạn được bảo mật',
            'start_privacy_text' => 'Thông tin chỉ được sử dụng để phục vụ việc kiểm tra điều kiện và tư vấn NOXH.',
            'start_privacy_icon' => 'lock',
            'start_agree_text' => 'Tôi đã đọc và đồng ý với',
            'start_agree_link_text' => 'chính sách bảo mật thông tin',
            'start_agree_link' => '/chinh-sach-bao-mat',
            'start_button' => 'Bắt đầu kiểm tra',
            'start_note' => '100% miễn phí  ·  Không lưu thông tin nếu bạn không đồng ý',
            'start_note_icon' => 'shield-tick',
            'start_disclaimer' => 'Kết quả chỉ mang tính tham khảo. Việc xác định đủ điều kiện mua NOXH được thực hiện dựa trên hồ sơ và quy định áp dụng tại thời điểm xét duyệt.',
            'start_disclaimer_icon' => 'info-line',

            // --- wizard (w-1.jpg) ---
            'wizard_heading' => 'Kiểm tra khả năng mua',
            'wizard_heading_blue' => 'Nhà ở xã hội',
            'wizard_subtitle' => '{so} câu hỏi đơn giản – Khoảng 2 phút – Biết ngay kết quả!',
            'wizard_image' => '/uploads/noxh/kiem-tra-dau-trang.jpg',

            'wizard_chip1_text' => 'Nhanh chóng',
            'wizard_chip1_icon' => 'bolt',
            'wizard_chip2_text' => 'Chính xác',
            'wizard_chip2_icon' => 'shield-check',
            'wizard_chip3_text' => 'Miễn phí',
            'wizard_chip3_icon' => 'money',
            'wizard_chip4_text' => 'Bảo mật thông tin',
            'wizard_chip4_icon' => 'lock',

            'wizard_step_text' => 'Câu {so}/{tong}',
            'wizard_back_text' => 'Quay lại',
            'wizard_next_text' => 'Tiếp theo',
            'wizard_finish_text' => 'Xem kết quả',
            'wizard_require_text' => 'Vui lòng chọn một đáp án trước khi đi tiếp.',

            'wizard_contact_heading' => 'Nhận kết quả',
            'wizard_contact_description' => 'Để lại thông tin để xem kết quả và được chuyên viên hỗ trợ.',
            'wizard_contact_name' => 'Họ và tên',
            'wizard_contact_phone' => 'Số điện thoại',
            'wizard_contact_note' => 'Số điện thoại sẽ được bảo mật, chỉ sử dụng để tư vấn các dự án phù hợp.',
            'wizard_contact_name_hint' => 'Nhập họ và tên (ví dụ: Nguyễn Văn A)',
            'wizard_contact_phone_hint' => 'Nhập số điện thoại (ví dụ: 0901 234 567)',
            'wizard_consent_text' => 'Tôi đồng ý để NOXH.vn liên hệ tư vấn về nhà ở xã hội và các thông tin liên quan phù hợp với nhu cầu của tôi.',
            'wizard_consent_link_text' => 'Xem chi tiết chính sách bảo mật',
            'wizard_consent_link' => '/chinh-sach-bao-mat',

            // --- khoi dia chi o buoc Nha o (nua duoi ban ve w-4) ---
            'wizard_addr_home_heading' => 'Địa chỉ nhà ở hiện tại',
            'wizard_addr_home_hint' => 'Chọn tỉnh/thành phố và phường/xã nơi bạn đang sinh sống thực tế.',
            'wizard_addr_work_heading' => 'Nơi làm việc hiện tại',
            'wizard_addr_work_hint' => 'Chọn tỉnh/thành phố và phường/xã nơi bạn đang làm việc.',
            'wizard_addr_province_label' => 'Tỉnh/Thành phố',
            'wizard_addr_ward_label' => 'Phường/Xã',
            'wizard_addr_province_hint' => '— Chọn tỉnh/thành phố —',
            'wizard_addr_ward_hint' => '— Chọn phường/xã —',
            'wizard_project_heading' => 'Các dự án NOXH trên địa bàn nơi bạn làm việc',
            'wizard_project_hint' => 'Chúng tôi đã tìm thấy các dự án Nhà ở xã hội tại {noi}. Bạn có thể chọn tối đa {max} dự án quan tâm.',
            'wizard_project_count_text' => 'Đã chọn: {so}/{max}',
            'wizard_project_link_text' => 'Xem thông tin',
            'wizard_project_empty_text' => 'Chưa có dự án nào trong khu vực này.',
            'wizard_project_max' => '5',

            // --- luat khoang cach (muc 3 cua cong-thuc.jpg) ---
            'wizard_dist_home_work_min' => '30',
            'wizard_dist_work_project_max' => '30',

            // --- trang ket qua: phan dung chung cho ca ba muc ---
            'result_label' => 'Kết quả kiểm tra sơ bộ',
            'result_back_text' => 'Quay về trang chủ',
            'result_time_text' => 'Kết quả được tạo lúc {gio} - {ngay}',
            'result_score_label' => 'Điểm đánh giá',
            'result_detail_heading' => 'Chi tiết kết quả theo từng tiêu chí',
            'result_badge_pass' => 'Phù hợp',
            'result_badge_unclear' => 'Cần xác minh',
            'result_badge_fail' => 'Chưa đáp ứng',
            'result_notice_heading' => 'Lưu ý quan trọng',
            'result_notice_lines' => "Kết quả này chỉ mang tính chất sơ bộ, tham khảo, dựa trên thông tin bạn cung cấp.\nViệc xác nhận chính thức sẽ do cơ quan có thẩm quyền thực hiện khi bạn nộp hồ sơ đầy đủ.\nQuy định có thể thay đổi theo từng thời điểm và từng địa phương/dự án.",
            'result_again_text' => 'Kiểm tra lại',
            'result_expert_text' => 'Tư vấn với chuyên gia ngay',
            'result_expert_link' => '/cong-hoa/tu-van',
            'result_suggest_heading' => 'Có thể bạn quan tâm',
            'result_suggest_description' => 'Một số dự án phù hợp với nhu cầu của bạn',
            'result_suggest_all_text' => 'Xem tất cả dự án',
            'result_suggest_price_text' => 'Chỉ từ',
            'result_banner_image' => '/uploads/noxh/kq-dai-duoi.jpg',
            'result_banner_quote' => '" Nhà ở xã hội - Vì cuộc sống tốt đẹp hơn "',

            // --- muc THANH CONG (thanh-cong.jpg) ---
            'result_high_image' => '/uploads/noxh/kq-high.png',
            'result_high_heading' => 'Bạn có khả năng đáp ứng điều kiện!',
            'result_high_description' => 'Dựa trên thông tin bạn cung cấp, bạn có khả năng đáp ứng các điều kiện mua nhà ở xã hội theo quy định hiện hành. Vui lòng liên hệ chuyên gia của NOXH.vn để được tư vấn chi tiết và hướng dẫn chuẩn bị hồ sơ.',
            'result_high_guide_text' => 'Xem hướng dẫn chuẩn bị hồ sơ',
            'result_high_guide_link' => '/ho-so',
            'result_high_side_image' => '/uploads/noxh/kq-tranh-high.png',
            'result_high_side1_heading' => 'Cơ hội an cư đang trong tầm tay!',
            'result_high_side1_body' => 'Bạn đã đáp ứng sơ bộ các điều kiện. Hãy để NOXH.vn đồng hành cùng bạn trong các bước tiếp theo.',
            'result_high_side1_lines' => "Tư vấn dự án phù hợp\nHướng dẫn chuẩn bị hồ sơ chi tiết\nCập nhật thông tin mới nhất\nHỗ trợ trong suốt quá trình đăng ký",
            'result_high_side1_icon' => 'house',
            'result_high_side1_tone' => 'green',

            // --- muc LUU Y (luu y.jpg) ---
            'result_medium_image' => '/uploads/noxh/kq-medium.png',
            'result_medium_heading' => 'Bạn cần kiểm tra thêm một số điều kiện',
            'result_medium_description' => 'Dựa trên thông tin bạn cung cấp, bạn có thể đáp ứng điều kiện mua nhà ở xã hội, nhưng cần làm rõ thêm một số thông tin hoặc bổ sung hồ sơ để có kết quả chính xác hơn. NOXH.vn sẵn sàng hỗ trợ bạn trong các bước tiếp theo.',
            'result_medium_guide_text' => 'Xem hướng dẫn chuẩn bị hồ sơ',
            'result_medium_guide_link' => '/ho-so',
            'result_medium_side_image' => '/uploads/noxh/kq-tranh-medium.png',
            'result_medium_side1_heading' => 'Bạn nên làm gì tiếp theo?',
            'result_medium_side1_lines' => "Liên hệ chuyên gia NOXH.vn để được tư vấn chi tiết\nChuẩn bị và bổ sung hồ sơ còn thiếu\nKiểm tra lại thông tin chưa rõ\nCập nhật quy định tại địa phương/dự án",
            'result_medium_side1_icon' => 'clipboard',
            'result_medium_side1_tone' => 'amber',
            'result_medium_side2_heading' => 'Gợi ý cho bạn',
            'result_medium_side2_body' => 'Trong nhiều trường hợp, bạn vẫn có thể đủ điều kiện nếu bổ sung thêm thông tin hoặc thuộc trường hợp đặc thù theo quy định hiện hành.',
            'result_medium_side2_icon' => 'bulb',
            'result_medium_side2_tone' => 'amber',

            // --- muc THAT BAI (that bai.jpg) ---
            'result_low_image' => '/uploads/noxh/kq-low.png',
            'result_low_heading' => 'Hiện có tiêu chí chưa đáp ứng',
            'result_low_description' => 'Dựa trên thông tin bạn cung cấp, hiện tại bạn chưa đáp ứng một số điều kiện mua nhà ở xã hội theo quy định hiện hành. Tuy nhiên, bạn vẫn có thể được tư vấn cụ thể để xem xét các phương án phù hợp khác.',
            'result_low_guide_text' => 'Xem quy định chi tiết',
            'result_low_guide_link' => '/phap-ly-noxh',
            'result_low_side_image' => '/uploads/noxh/kq-tranh-low.png',
            'result_low_side1_heading' => 'Lý do chưa đáp ứng',
            'result_low_side1_body' => 'Xem bảng tiêu chí bên trái: những dòng đánh dấu "Chưa đáp ứng" là phần bạn cần xem lại.',
            'result_low_side1_icon' => 'warning',
            'result_low_side1_tone' => 'rose',
            'result_low_side2_heading' => 'Gợi ý cho bạn',
            'result_low_side2_lines' => "Kiểm tra lại thông tin nếu bạn cho rằng có nhầm lẫn\nTìm hiểu các chương trình nhà ở phù hợp khác\nLiên hệ chuyên gia NOXH.vn để được tư vấn phương án cụ thể",
            'result_low_side2_icon' => 'bulb',
            'result_low_side2_tone' => 'amber',
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

        $this->command?->info("Da them {$them} o noi dung cho bo kiem tra dieu kien.");

        $this->doiChuCu();
    }

    /**
     * Sua vai o chu do CHINH SEEDER nay dat sai o ban truoc.
     *
     * Chi ghi de khi o do van dung y nguyen cau cu - quan tri da sua tay thi
     * de nguyen, khong ai muon mo lai trang thay chu minh vua sua bien mat.
     */
    private function doiChuCu(): void
    {
        $doi = [
            'wizard_contact_note' => [
                'Thông tin bạn cung cấp được bảo mật tuyệt đối và chỉ sử dụng để kiểm tra điều kiện mua nhà ở xã hội.',
                'Số điện thoại sẽ được bảo mật, chỉ sử dụng để tư vấn các dự án phù hợp.',
            ],
            'wizard_contact_heading' => ['Nhận kết quả', 'Nhận kết quả kiểm tra của anh/chị.'],
            'wizard_chip3_text' => ['Bảo mật thông tin', 'Miễn phí'],
            'wizard_chip3_icon' => ['lock', 'money'],
        ];

        foreach ($doi as $khoa => [$cu, $moi]) {
            DB::table('introduces')->where('keyword', $khoa)->where('language_id', 1)
                ->where('content', $cu)->update(['content' => $moi]);
        }
    }

    /**
     * Ten buoc, dong luu y va thu tu buoc.
     *
     * Thu tu GHI DE: ban ve dat "Doi tuong" o buoc 1 con bang cu de o buoc 6,
     * ma thu tu la thu quyet dinh trang nhin giong hay khong giong ban ve.
     */
    private function napBuoc(): void
    {
        $sua = 0;

        foreach (self::BUOC as $nhanDang => [$thuTu, $tenBuoc, $luuY]) {
            $cau = DB::table('eligibility_questions')
                ->whereNull('deleted_at')
                ->where(function ($q) use ($nhanDang) {
                    foreach (explode('|', $nhanDang) as $doan) {
                        $q->orWhere('question', 'LIKE', '%' . $doan . '%');
                    }
                })
                ->first();

            if (!$cau) {
                $this->command?->warn("Khong thay cau hoi chua '{$nhanDang}'");
                continue;
            }

            DB::table('eligibility_questions')->where('id', $cau->id)->update([
                'order' => $thuTu,
                'step_label' => $cau->step_label ?: $tenBuoc,
                'foot_note' => $cau->foot_note ?: $luuY,
                'required' => 1,
                'updated_at' => now(),
            ]);

            $sua++;
        }

        $this->command?->info("Da dat ten va thu tu cho {$sua} buoc.");
    }

    /**
     * Bo 12 dap an cua cau "Doi tuong".
     *
     * Bang cu chi co 7 dap an gop chung ("Ho ngheo, can ngheo" mot dong), ban
     * ve tach ra 12 o co hinh rieng. Dong bo han theo ban ve: dap an cu nao
     * khong con trong danh sach thi xoa, con lai cap nhat tai cho de khong
     * lam dut lien ket voi cac luot kiem tra da luu.
     */
    private function napDoiTuong(): void
    {
        $cau = DB::table('eligibility_questions')
            ->whereNull('deleted_at')
            ->where('question', 'LIKE', '%nhóm đối tượng%')
            ->first();

        if (!$cau) {
            $this->command?->warn('Chua co cau hoi "nhom doi tuong".');
            return;
        }

        // Cau hoi trong ban ve day du hon cau cu.
        DB::table('eligibility_questions')->where('id', $cau->id)->update([
            'question' => 'Anh/chị thuộc nhóm đối tượng nào để đăng ký mua nhà ở xã hội?',
            'hint' => 'Vui lòng chọn nhóm phù hợp nhất với trường hợp của anh/chị.',
            'input_type' => 'select',
            'updated_at' => now(),
        ]);

        $giuLai = [];

        foreach (self::DOI_TUONG as $i => [$gia, $nhan, $ghi, $hinh, $mau]) {
            [$ketLuan, $diem] = self::KET_LUAN_DOI_TUONG[$gia] ?? ['pass', 3];

            // Tranh cat tu ban ve. Hai nhom bi net but do de len khong cat
            // ra duoc (xem tools/tach-anh-ban-ve.py) - de trong thi trang
            // ngoai lui ve ve hinh net trong vong tron mau.
            $anh = '/uploads/noxh/dt-' . $gia . '.png';
            $anh = is_file(public_path(ltrim($anh, '/'))) ? $anh : null;

            $dong = [
                'eligibility_question_id' => $cau->id,
                'label' => $nhan,
                'image' => $anh,
                'icon' => $hinh,
                'icon_tone' => $mau,
                'verdict' => $ketLuan,
                'score' => $diem,
                // Dong chu nho duoi nhan - ban ve chi in o ba o, cac o con
                // lai de trong chu KHONG in ly do ket luan vao day.
                'note' => $ghi,
                'order' => $i,
                'updated_at' => now(),
            ];

            $cu = DB::table('eligibility_options')
                ->where('eligibility_question_id', $cau->id)
                ->where('value', $gia)
                ->first();

            if ($cu) {
                DB::table('eligibility_options')->where('id', $cu->id)->update($dong);
                $giuLai[] = $cu->id;
                continue;
            }

            $giuLai[] = DB::table('eligibility_options')->insertGetId(
                $dong + ['value' => $gia, 'created_at' => now()]
            );
        }

        $xoa = DB::table('eligibility_options')
            ->where('eligibility_question_id', $cau->id)
            ->whereNotIn('id', $giuLai)
            ->count();

        if ($xoa) {
            // Cac luot kiem tra cu tro toi dap an bi bo: go lien ket chu khong
            // xoa luot, ket luan va diem da luu ngay tren dong tra loi roi.
            $id = DB::table('eligibility_options')
                ->where('eligibility_question_id', $cau->id)
                ->whereNotIn('id', $giuLai)
                ->pluck('id');

            DB::table('eligibility_answers')->whereIn('eligibility_option_id', $id)
                ->update(['eligibility_option_id' => null]);

            DB::table('eligibility_options')->whereIn('id', $id)->delete();
        }

        $this->command?->info('Da dong bo 12 nhom doi tuong (' . $xoa . ' dap an cu bi bo).');
    }


    /**
     * Cau hoi thu nhap chuyen sang bo cuc "chia theo tinh huong".
     *
     * Ban ve w-2.jpg bo bang nam muc thu nhap cu, thay bang ba tam: doc than,
     * doc than nuoi con nho, da ket hon - moi tam hai muc "khong qua X" va
     * "tren X". Dong bo han theo ban ve nhung KHONG xoa dap an cu neu da co
     * luot kiem tra tro toi: chi go khoi tam va de quan tri tu quyet.
     */
    private function napThuNhap(): void
    {
        $cau = DB::table('eligibility_questions')
            ->whereNull('deleted_at')
            ->where('question', 'LIKE', '%Tổng thu nhập bình quân%')
            ->orWhere('question', 'LIKE', '%Mức thu nhập hàng tháng%')
            ->first();

        if (!$cau) {
            $this->command?->warn('Chua co cau hoi thu nhap.');
            return;
        }

        DB::table('eligibility_questions')->where('id', $cau->id)->update([
            'question' => 'Mức thu nhập hàng tháng của anh/chị?',
            'hint' => 'Vui lòng chọn mức thu nhập phù hợp với trường hợp của bạn.',
            'layout' => 'matrix',
            'image' => $this->anh('kt-tien.png'),
            'icon' => 'coins',
            'icon_tone' => 'sky',
            'foot_note' => 'Thu nhập được xem xét theo bình quân 12 tháng liền kề, theo quy định hiện hành.',
            'foot_note_sub' => 'Đây là thông tin để đánh giá sơ bộ. Cơ quan có thẩm quyền sẽ xác nhận khi bạn nộp hồ sơ.',
            'updated_at' => now(),
        ]);

        $giuTam = [];
        $giuDapAn = [];

        foreach (self::THU_NHAP as $i => [$ma, $ten, $ghi, $tranh, $hinh, $mau, $nguong]) {
            $tam = DB::table('eligibility_option_groups')
                ->where('eligibility_question_id', $cau->id)
                ->where('label', $ten)
                ->first();

            $dong = [
                'eligibility_question_id' => $cau->id,
                'label' => $ten,
                'note' => $ghi,
                'image' => $this->anh($tranh),
                'icon' => $hinh,
                'tone' => $mau,
                'order' => $i,
                'updated_at' => now(),
            ];

            if ($tam) {
                DB::table('eligibility_option_groups')->where('id', $tam->id)->update($dong);
                $tamId = $tam->id;
            } else {
                $tamId = DB::table('eligibility_option_groups')->insertGetId($dong + ['created_at' => now()]);
            }

            $giuTam[] = $tamId;

            $muc = [
                [$ma . '-duoi', 'Không quá ' . $nguong . ' triệu/tháng', 'pass', 3, 0],
                [$ma . '-tren', 'Trên ' . $nguong . ' triệu/tháng', 'fail', 0, 1],
            ];

            foreach ($muc as [$gia, $nhan, $ketLuan, $diem, $thuTu]) {
                $dongDapAn = [
                    'eligibility_question_id' => $cau->id,
                    'eligibility_option_group_id' => $tamId,
                    'label' => $nhan,
                    'verdict' => $ketLuan,
                    'score' => $diem,
                    'order' => $i * 10 + $thuTu,
                    'updated_at' => now(),
                ];

                $cu = DB::table('eligibility_options')
                    ->where('eligibility_question_id', $cau->id)
                    ->where('value', $gia)
                    ->first();

                if ($cu) {
                    DB::table('eligibility_options')->where('id', $cu->id)->update($dongDapAn);
                    $giuDapAn[] = $cu->id;
                    continue;
                }

                $giuDapAn[] = DB::table('eligibility_options')
                    ->insertGetId($dongDapAn + ['value' => $gia, 'created_at' => now()]);
            }
        }

        // Nam muc thu nhap cu cua bo cau hoi mau khong con cho dung trong ban
        // ve moi. Xoa han, neu chi go khoi tam thi chung roi xuong duoi ba
        // tam va nguoi dung thay hai bo muc thu nhap chong nhau.
        $id = DB::table('eligibility_options')
            ->where('eligibility_question_id', $cau->id)
            ->whereNotIn('id', $giuDapAn)
            ->pluck('id');

        if ($id->count()) {
            // Luot kiem tra cu tro toi dap an bi bo: go lien ket chu khong
            // xoa luot - ket luan va diem da luu ngay tren dong tra loi roi.
            DB::table('eligibility_answers')->whereIn('eligibility_option_id', $id)
                ->update(['eligibility_option_id' => null]);

            DB::table('eligibility_options')->whereIn('id', $id)->delete();
        }

        $this->command?->info('Da dung 3 tinh huong cho cau thu nhap (' . $id->count() . ' dap an cu bi bo).');
    }

    /** Duong dan anh neu file co that, khong thi tra ve null. */
    private function anh(string $ten): ?string
    {
        $duong = '/uploads/noxh/' . $ten;

        return is_file(public_path(ltrim($duong, '/'))) ? $duong : null;
    }


    /**
     * Buoc "Chinh sach" - ban ve noxh_image/w-3.jpg.
     *
     * Ba dap an xep doc, moi dap an mot hinh vuong va mot doan mo ta. Dap an
     * "Da tung duoc ho tro" mang co DUNG SOM: chon no la biet chac khong du
     * dieu kien, khong hoi tiep buoc Nha o nua.
     */
    private function napChinhSach(): void
    {
        $cau = $this->timCau('%hỗ trợ nhà ở dưới mọi hình thức%', '%chính sách hỗ trợ về nhà ở%');

        if (!$cau) {
            $this->command?->warn('Chua co cau hoi chinh sach.');
            return;
        }

        DB::table('eligibility_questions')->where('id', $cau->id)->update([
            'question' => 'Anh/chị hoặc vợ/chồng đã từng được hưởng chính sách hỗ trợ về nhà ở chưa?',
            'hint' => 'Bao gồm: được cấp nhà ở, mua/thuê mua nhà ở xã hội hoặc nhận hỗ trợ nhà ở từ các chương trình của Nhà nước.',
            'layout' => 'list',
            'foot_note' => 'Lưu ý: Thông tin này giúp NOXH.vn xác định sơ bộ khả năng đáp ứng điều kiện mua nhà ở xã hội theo quy định hiện hành.',
            'updated_at' => now(),
        ]);

        // [gia tri, nhan, mo ta, anh, hinh du phong, mau, ket luan, diem, dung som]
        $ds = [
            ['chua-ho-tro', 'Chưa từng được hỗ trợ',
                'Chưa mua, chưa thuê mua và chưa được hưởng chính sách hỗ trợ nhà ở từ Nhà nước.',
                'cs-chua-ho-tro.png', 'doc-line', 'rose', 'pass', 2, 0],
            ['da-ho-tro', 'Đã từng được hỗ trợ',
                'Đã từng được cấp nhà ở, mua/thuê mua nhà ở xã hội hoặc nhận hỗ trợ nhà ở từ các chương trình của Nhà nước.',
                'cs-da-ho-tro.png', 'home-door', 'green', 'fail', 0, 1],
            ['khong-chac', 'Không chắc chắn',
                'Chưa rõ trường hợp của mình.',
                'cs-khong-chac.png', 'question', 'amber', 'unclear', 0, 0],
        ];

        $this->dongBoDapAn($cau->id, $ds);

        $this->command?->info('Da dung buoc Chinh sach (3 dap an).');
    }

    /**
     * Buoc "Nha o" - ban ve noxh_image/w-4.jpg.
     *
     * Ba LOAI nha o ve thanh ba the lon. So luong khong co dinh la ba: quan
     * tri them bot o man hinh "Dap an dieu kien", luoi tu chia lai.
     */
    private function napNhaO(): void
    {
        $cau = $this->timCau('%đã có nhà ở thuộc sở hữu%', '%có nhà ở thuộc sở hữu không%');

        if (!$cau) {
            $this->command?->warn('Chua co cau hoi nha o.');
            return;
        }

        DB::table('eligibility_questions')->where('id', $cau->id)->update([
            'question' => 'Hiện tại anh/chị có nhà ở thuộc sở hữu không?',
            'hint' => 'Thông tin này giúp chúng tôi đánh giá đúng điều kiện theo quy định hiện hành.',
            'layout' => 'card',
            'extras' => 'address',
            'image' => $this->anh('kt-nha.png'),
            'icon' => 'house',
            'icon_tone' => 'sky',
            'foot_note' => 'Vui lòng cung cấp địa điểm nhà ở hiện tại và nơi làm việc để chúng tôi kiểm tra điều kiện theo quy định.',
            'foot_note_sub' => '',
            'updated_at' => now(),
        ]);

        $ds = [
            ['chua-co-nha', 'Chưa có nhà ở', 'Chưa sở hữu nhà ở trên toàn quốc',
                'nh-chua-co-nha.png', 'home', 'slate', 'pass', 2, 0],
            ['co-dat', 'Có đất nhưng chưa có nhà', 'Có quyền sử dụng đất nhưng chưa có nhà ở',
                'nh-co-dat.png', 'area', 'green', 'unclear', 1, 0],
            ['co-nha', 'Có nhà ở', 'Đang sở hữu nhà ở',
                'nh-co-nha.png', 'house', 'rose', 'fail', 0, 0],
        ];

        $this->dongBoDapAn($cau->id, $ds);

        $this->command?->info('Da dung buoc Nha o (3 loai).');
    }

    /**
     * Buoc cuoi - o nhap ho ten, so dien thoai (ban ve noxh_image/w-5.jpg).
     *
     * Buoc nay KHONG phai cau hoi: bo het dap an di de no khong bi cham diem
     * va khong doi nguoi dung chon gi.
     */
    private function napNhapTin(): void
    {
        $cau = $this->timCau('%kết hôn chưa%', '%Nhận kết quả kiểm tra%');

        if (!$cau) {
            $this->command?->warn('Chua co cau de lam buoc nhap tin.');
            return;
        }

        DB::table('eligibility_questions')->where('id', $cau->id)->update([
            'question' => 'Nhận kết quả kiểm tra của anh/chị.',
            'step_label' => 'Kết quả',
            'hint' => 'Vui lòng nhập thông tin để xem kết quả và nhận tư vấn các dự án nhà ở xã hội phù hợp.',
            'layout' => 'contact',
            'image' => $this->anh('kt-ho-so.png'),
            'icon' => 'clipboard',
            'icon_tone' => 'sky',
            'foot_note' => '',
            'foot_note_sub' => '',
            'required' => 0,
            'order' => 4,
            'publish' => 2,
            'updated_at' => now(),
        ]);

        $id = DB::table('eligibility_options')->where('eligibility_question_id', $cau->id)->pluck('id');

        if ($id->count()) {
            DB::table('eligibility_answers')->whereIn('eligibility_option_id', $id)
                ->update(['eligibility_option_id' => null]);
            DB::table('eligibility_options')->whereIn('id', $id)->delete();
        }

        $this->command?->info('Da dung buoc nhap tin (' . $id->count() . ' dap an cu bi bo).');
    }

    /**
     * An cac buoc khong con trong ban ve.
     *
     * AN chu khong xoa: cau hoi va dap an cu con duoc cac luot kiem tra da
     * luu tro toi, xoa di la bang ket qua thung lo. Quan tri bat lai duoc
     * bat cu luc nao.
     */
    private function anBuocThua(): void
    {
        $con = DB::table('eligibility_questions')
            ->whereNull('deleted_at')
            ->where('publish', 2)
            ->whereNotIn('layout', ['contact'])
            ->where(function ($q) {
                $q->where('question', 'LIKE', '%Số thành viên%')
                    ->orWhere('question', 'LIKE', '%nơi có dự án NOXH?%')
                    ->orWhere('question', 'LIKE', '%thuế thu nhập cá nhân%');
            })
            ->update(['publish' => 1, 'updated_at' => now()]);

        $this->command?->info("Da an {$con} buoc khong co trong ban ve.");
    }

    /**
     * Cac tam o cot phai cua buoc 3, 4, 5 (ban ve w-3, w-4, w-5).
     *
     * Tra theo tieu de trong cung mot buoc nen chay lai khong sinh ra ban
     * trung; tam chi co tranh thi tra theo duong dan anh.
     */
    private function napTamCotPhai(): void
    {
        $tam = [
            ['%chính sách hỗ trợ về nhà ở%', [
                ['Vì sao cần thông tin này?', '', "Đối chiếu điều kiện theo quy định hiện hành.\nTránh trùng lặp chính sách hỗ trợ.\nGiúp tư vấn chính xác và nhanh chóng hơn.\nThông tin được bảo mật tuyệt đối.", '', 'info', 'sky', 0],
                ['', '', '', 'kt-tam-gia-dinh.png', '', 'sky', 1],
            ]],
            ['%có nhà ở thuộc sở hữu không%', [
                ['Thông tin của bạn luôn được bảo mật', '', "Chỉ sử dụng để tư vấn dự án phù hợp\nKhông chia sẻ cho bên thứ 3\nDữ liệu được mã hóa, bảo mật theo quy định", '', 'shield-check', 'green', 0],
                ['Vì sao cần thông tin này?', "Một số trường hợp có nhà ở nhưng cách nơi làm việc từ 30 km trở lên, đồng thời dự án nhà ở xã hội cách nơi làm việc không quá 30 km vẫn có thể được xem xét mua nhà ở xã hội theo quy định của từng địa phương.\n\nChúng tôi sử dụng dữ liệu địa giới hành chính mới từ VNeID để tính toán khoảng cách chính xác và thuận tiện cho bạn.", '', '', 'bulb', 'amber', 1],
                ['', '', '', 'kt-tam-toa-nha.png', '', 'sky', 2],
            ]],
            ['%Nhận kết quả kiểm tra%', [
                ['Thông tin của bạn luôn được bảo mật', '', "Chỉ sử dụng để tư vấn dự án phù hợp\nKhông chia sẻ cho bên thứ 3\nBạn có thể yêu cầu xóa thông tin bất cứ lúc nào", '', 'shield-check', 'amber', 0],
                ['', '', '', 'kt-tam-dien-thoai.png', '', 'amber', 1],
                ['Sau khi xem kết quả', 'Chuyên viên NOXH.vn sẽ sớm liên hệ với bạn để tư vấn chi tiết các dự án phù hợp.', '', '', 'bulb', 'amber', 2],
            ]],
        ];

        $them = 0;

        foreach ($tam as [$nhanDang, $ds]) {
            $cau = $this->timCau($nhanDang);

            if (!$cau) {
                continue;
            }

            foreach ($ds as [$dau, $chu, $y, $anh, $hinh, $mau, $thuTu]) {
                $dong = [
                    'eligibility_question_id' => $cau->id,
                    'heading' => $dau,
                    'body' => $chu,
                    'bullets' => $y,
                    'image' => $anh !== '' ? $this->anh($anh) : null,
                    'icon' => $hinh,
                    'tone' => $mau,
                    'order' => $thuTu,
                    'updated_at' => now(),
                ];

                $cu = DB::table('eligibility_panels')
                    ->where('eligibility_question_id', $cau->id)
                    ->where('order', $thuTu)
                    ->first();

                if ($cu) {
                    DB::table('eligibility_panels')->where('id', $cu->id)->update($dong);
                    continue;
                }

                DB::table('eligibility_panels')->insert($dong + ['created_at' => now()]);
                $them++;
            }
        }

        $this->command?->info("Da them {$them} tam cot phai.");
    }

    // -------------------------------------------------------------------------


    /**
     * Sau tieu chi in tren trang ket qua.
     *
     * Chep thang tu ba ban ve thanh-cong.jpg / luu y.jpg / that bai.jpg.
     * Bon tieu chi dau lay ket luan tu bon buoc cau hoi, "Khu vuc cu tru/lam
     * viec" tinh tu khoang cach, "Ho so co ban" khong hoi ai ca nen luon dat.
     */
    private function napTieuChi(): void
    {
        // [ten, nguon, doan chu nhan dang buoc, mo ta khi dat / can xac minh / chua dap ung]
        $ds = [
            ['Nhóm đối tượng', 'question', 'nhóm đối tượng',
                'Bạn thuộc nhóm đối tượng được mua nhà ở xã hội',
                'Cần xác định rõ nhóm đối tượng của bạn',
                'Bạn chưa thuộc nhóm đối tượng được mua nhà ở xã hội'],
            ['Điều kiện về nhà ở', 'question', 'có nhà ở thuộc sở hữu không',
                'Chưa có nhà ở thuộc sở hữu của mình (hoặc thuộc trường hợp được xét theo khoảng cách)',
                'Cần kiểm tra thêm thông tin về tình trạng nhà ở hiện tại hoặc giấy tờ liên quan',
                'Bạn đang có nhà ở thuộc sở hữu và không thuộc trường hợp được xét theo khoảng cách'],
            ['Điều kiện thu nhập', 'question', 'thu nhập hàng tháng',
                'Tổng thu nhập trong ngưỡng quy định',
                'Cần đối chiếu lại mức thu nhập với ngưỡng quy định',
                'Tổng thu nhập vượt ngưỡng quy định'],
            ['Khu vực cư trú/làm việc', 'area', '',
                'Phù hợp với phạm vi dự án',
                'Chưa đủ thông tin nơi làm việc hoặc dự án quan tâm để đối chiếu',
                'Nơi làm việc cách dự án quá xa so với quy định'],
            ['Chính sách đã hưởng', 'question', 'chính sách hỗ trợ về nhà ở',
                'Chưa từng được hỗ trợ nhà ở từ Nhà nước',
                'Cần xác minh lại các chính sách hỗ trợ đã hưởng',
                'Đã từng được hưởng chính sách hỗ trợ về nhà ở'],
            ['Hồ sơ cơ bản', 'fixed', '',
                'Bạn đã chuẩn bị hoặc có thể chuẩn bị đầy đủ theo hướng dẫn',
                'Cần bổ sung hoặc kiểm tra thêm một số giấy tờ',
                'Hồ sơ chưa đáp ứng yêu cầu'],
        ];

        $them = 0;

        foreach ($ds as $i => [$ten, $nguon, $nhanDang, $dat, $chuaRo, $khongDat]) {
            $cauId = null;

            if ($nguon === 'question' && $nhanDang !== '') {
                $cau = $this->timCau('%' . $nhanDang . '%');
                $cauId = $cau->id ?? null;
            }

            $dong = [
                'label' => $ten,
                'source' => $nguon,
                'eligibility_question_id' => $cauId,
                'pass_text' => $dat,
                'unclear_text' => $chuaRo,
                'fail_text' => $khongDat,
                'order' => $i,
                'publish' => 2,
                'updated_at' => now(),
            ];

            $cu = DB::table('eligibility_criteria')->where('label', $ten)->first();

            if ($cu) {
                DB::table('eligibility_criteria')->where('id', $cu->id)->update($dong);
                continue;
            }

            DB::table('eligibility_criteria')->insert($dong + ['created_at' => now()]);
            $them++;
        }

        // Muc 3 cua cong thuc: dang co nha o VAN duoc mua neu nha cach noi
        // lam viec >= 30km va noi lam viec cach du an <= 30km.
        DB::table('eligibility_options')->where('value', 'co-nha')
            ->update(['needs_distance' => 1, 'updated_at' => now()]);

        $this->command?->info("Da dung 6 tieu chi cham ket qua ({$them} dong moi).");
    }

    /** Tim cau hoi theo mot trong cac doan chu nhan dang. */
    private function timCau(string ...$doan)
    {
        return DB::table('eligibility_questions')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($doan) {
                foreach ($doan as $d) {
                    $q->orWhere('question', 'LIKE', $d);
                }
            })
            ->first();
    }

    /**
     * Dong bo bo dap an cua mot cau theo ban ve.
     *
     * Dap an cu khong con trong ban ve thi xoa, nhung go lien ket o bang tra
     * loi truoc - ket luan va diem da luu ngay tren dong tra loi roi nen cac
     * luot kiem tra cu van doc duoc.
     */
    private function dongBoDapAn(int $cauId, array $ds): void
    {
        $giu = [];

        foreach ($ds as $i => [$gia, $nhan, $moTa, $anh, $hinh, $mau, $ketLuan, $diem, $dungSom]) {
            $dong = [
                'eligibility_question_id' => $cauId,
                'eligibility_option_group_id' => null,
                'label' => $nhan,
                'note' => $moTa,
                'image' => $this->anh($anh),
                'icon' => $hinh,
                'icon_tone' => $mau,
                'verdict' => $ketLuan,
                'stop_flow' => $dungSom,
                'score' => $diem,
                'order' => $i,
                'updated_at' => now(),
            ];

            $cu = DB::table('eligibility_options')
                ->where('eligibility_question_id', $cauId)
                ->where('value', $gia)
                ->first();

            if ($cu) {
                DB::table('eligibility_options')->where('id', $cu->id)->update($dong);
                $giu[] = $cu->id;
                continue;
            }

            $giu[] = DB::table('eligibility_options')->insertGetId($dong + ['value' => $gia, 'created_at' => now()]);
        }

        $bo = DB::table('eligibility_options')
            ->where('eligibility_question_id', $cauId)
            ->whereNotIn('id', $giu)
            ->pluck('id');

        if ($bo->count()) {
            DB::table('eligibility_answers')->whereIn('eligibility_option_id', $bo)
                ->update(['eligibility_option_id' => null]);
            DB::table('eligibility_options')->whereIn('id', $bo)->delete();
        }
    }

    /** Hinh cho dap an cua cac cau con lai - o nao da co hinh thi de nguyen. */
    private function napHinhDapAn(): void
    {
        $sua = 0;

        foreach (self::HINH_DAP_AN as $gia => [$hinh, $mau]) {
            $sua += DB::table('eligibility_options')
                ->where('value', $gia)
                ->where(function ($q) {
                    $q->whereNull('icon')->orWhere('icon', '');
                })
                ->update(['icon' => $hinh, 'icon_tone' => $mau, 'updated_at' => now()]);
        }

        $this->command?->info("Da dat hinh cho {$sua} dap an cua cac buoc con lai.");
    }
}
