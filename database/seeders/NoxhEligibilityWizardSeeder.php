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
     * Khoa la mot doan chu co trong cau hoi (khong dau cach dau dong) - tra
     * theo id thi may khac chay ra so khac.
     *
     * [doan chu nhan dang cau hoi] => [thu tu, ten buoc, dong luu y]
     */
    private const BUOC = [
        'nhóm đối tượng' => [0, 'Đối tượng', 'Danh mục nhóm đối tượng được xây dựng theo quy định hiện hành. Vui lòng chọn đúng nhóm phù hợp.'],
        'đã có nhà ở thuộc sở hữu' => [1, 'Nhà ở', 'Xét nhà ở thuộc sở hữu của anh/chị hoặc vợ/chồng tại tỉnh/thành phố nơi có dự án.'],
        'Tổng thu nhập bình quân' => [2, 'Thu nhập', 'Mức thu nhập xét theo bình quân hàng tháng của hộ gia đình trong 01 năm liền kề.'],
        'nơi có dự án NOXH?' => [3, 'Dự án', 'Anh/chị chỉ được đăng ký mua tại tỉnh/thành phố đang cư trú hoặc làm việc.'],
        'hỗ trợ nhà ở dưới mọi hình thức' => [4, 'Chính sách', 'Đã nhận hỗ trợ nhà ở của Nhà nước thì không được xét mua nhà ở xã hội lần nữa.'],
        'Số thành viên trong hộ' => [5, 'Hộ gia đình', 'Số thành viên dùng để đối chiếu ngưỡng thu nhập của hộ gia đình.'],
        'thuế thu nhập cá nhân' => [6, 'Hồ sơ', 'Thông tin này dùng để đối chiếu với mức thu nhập anh/chị đã khai.'],
        'kết hôn' => [7, 'Kết quả', 'Tình trạng hôn nhân quyết định ngưỡng thu nhập áp dụng: người độc thân hay hai vợ chồng.'],
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
        $this->napHinhDapAn();
    }

    // -------------------------------------------------------------------------

    private function napChu(): void
    {
        $chu = [
            'wizard_heading' => 'Kiểm tra khả năng mua',
            'wizard_heading_blue' => 'Nhà ở xã hội',
            'wizard_subtitle' => '{so} câu hỏi đơn giản – Khoảng 2 phút – Biết ngay kết quả!',
            'wizard_image' => '/uploads/noxh/kiem-tra-dau-trang.jpg',

            'wizard_chip1_text' => 'Nhanh chóng',
            'wizard_chip1_icon' => 'clock',
            'wizard_chip2_text' => 'Chính xác',
            'wizard_chip2_icon' => 'shield-check',
            'wizard_chip3_text' => 'Bảo mật thông tin',
            'wizard_chip3_icon' => 'lock',

            'wizard_step_text' => 'Câu {so}/{tong}',
            'wizard_back_text' => 'Quay lại',
            'wizard_next_text' => 'Tiếp theo',
            'wizard_finish_text' => 'Xem kết quả',
            'wizard_require_text' => 'Vui lòng chọn một đáp án trước khi đi tiếp.',

            'wizard_contact_heading' => 'Nhận kết quả',
            'wizard_contact_description' => 'Để lại thông tin để xem kết quả và được chuyên viên hỗ trợ.',
            'wizard_contact_name' => 'Họ và tên',
            'wizard_contact_phone' => 'Số điện thoại',
            'wizard_contact_note' => 'Thông tin bạn cung cấp được bảo mật tuyệt đối và chỉ sử dụng để kiểm tra điều kiện mua nhà ở xã hội.',
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
                ->where('question', 'LIKE', '%' . $nhanDang . '%')
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

            $dong = [
                'eligibility_question_id' => $cau->id,
                'label' => $nhan,
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
