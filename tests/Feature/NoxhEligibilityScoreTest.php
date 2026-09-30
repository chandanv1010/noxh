<?php

namespace Tests\Feature;

use App\Classes\NoxhKhoangCach;
use App\Models\EligibilityCheck;
use App\Models\EligibilityCriterion;
use App\Models\EligibilityQuestion;
use App\Services\V1\Eligibility\EligibilityScoreService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Cham diem theo tieu chi va luat khoang cach
 * (file noxh_image/cong-thuc.jpg + ba ban ve ket qua).
 */
class NoxhEligibilityScoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        NoxhKhoangCach::quen();
    }

    public function test_co_du_sau_tieu_chi(): void
    {
        $ds = EligibilityCriterion::where('publish', 2)->orderBy('order')->get();

        $this->assertCount(6, $ds, 'Ba ban ve ket qua deu in sau tieu chi');

        // Moi tieu chi phai khai ro lay ket luan tu dau.
        foreach ($ds as $tc) {
            $this->assertContains($tc->source, array_keys(EligibilityCriterion::NGUON));

            if ($tc->source === 'question') {
                $this->assertNotNull($tc->eligibility_question_id, "Tieu chi {$tc->label} chua gan buoc nao");
            }
        }
    }

    /** Tra loi dat het + dia chi hop le -> thanh cong 6/6. */
    public function test_tra_loi_dat_het_thi_ra_muc_thanh_cong(): void
    {
        $luot = $this->chay([
            'nhom-doi-tuong' => 'cach-mang',
            'thu-nhap' => 'doc-than-duoi',
            'chinh-sach' => 'chua-ho-tro',
            'nha-o' => 'chua-co-nha',
        ], $this->diaChiCungTinh(), '0900000101');

        try {
            $this->assertSame('high', $luot->result_level);
            $this->assertSame(6, (int) $luot->criteria_total);
            $this->assertSame(6, (int) $luot->criteria_passed);

            foreach ($luot->tieuChi() as $tc) {
                $this->assertSame('pass', $tc['verdict'], 'Tieu chi ' . $tc['label'] . ' khong dat');
            }
        } finally {
            $this->don($luot);
        }
    }

    /**
     * Muc 3 cua cong thuc: dang CO NHA O van duoc mua neu nha cach noi lam
     * viec >= 30km va noi lam viec cach du an <= 30km.
     *
     * Nha va noi lam viec cung mot tinh -> khoang cach 0 -> khong qua nguong
     * 30km -> tieu chi nha o KHONG dat.
     */
    public function test_co_nha_o_cung_tinh_voi_noi_lam_viec_thi_khong_dat(): void
    {
        $luot = $this->chay([
            'nhom-doi-tuong' => 'cach-mang',
            'thu-nhap' => 'doc-than-duoi',
            'chinh-sach' => 'chua-ho-tro',
            'nha-o' => 'co-nha',
        ], $this->diaChiCungTinh(), '0900000102');

        try {
            $nhaO = $this->tieuChi($luot, 'Điều kiện về nhà ở');

            $this->assertSame('fail', $nhaO['verdict']);
            $this->assertSame('low', $luot->result_level);
        } finally {
            $this->don($luot);
        }
    }

    /**
     * Van co nha o nhung nha o TINH KHAC, xa noi lam viec, ma noi lam viec
     * lai sat du an -> dat.
     */
    public function test_co_nha_o_xa_noi_lam_viec_va_gan_du_an_thi_van_dat(): void
    {
        $xa = $this->diaChiXaNha();

        if (!$xa) {
            $this->markTestSkipped('Chua co du an nao de dung hai tinh cach nhau > 30km.');
        }

        $luot = $this->chay([
            'nhom-doi-tuong' => 'cach-mang',
            'thu-nhap' => 'doc-than-duoi',
            'chinh-sach' => 'chua-ho-tro',
            'nha-o' => 'co-nha',
        ], $xa, '0900000103');

        try {
            $this->assertSame('pass', $this->tieuChi($luot, 'Điều kiện về nhà ở')['verdict']);
            $this->assertSame('pass', $this->tieuChi($luot, 'Khu vực cư trú/làm việc')['verdict']);
        } finally {
            $this->don($luot);
        }
    }

    /** Chua khai dia chi thi bao CAN XAC MINH, khong danh truot. */
    public function test_chua_khai_dia_chi_thi_bao_can_xac_minh(): void
    {
        $luot = $this->chay([
            'nhom-doi-tuong' => 'cach-mang',
            'thu-nhap' => 'doc-than-duoi',
            'chinh-sach' => 'chua-ho-tro',
            'nha-o' => 'co-nha',
        ], [], '0900000104');

        try {
            $this->assertSame('unclear', $this->tieuChi($luot, 'Điều kiện về nhà ở')['verdict']);
            $this->assertSame('unclear', $this->tieuChi($luot, 'Khu vực cư trú/làm việc')['verdict']);
            $this->assertSame('medium', $luot->result_level);
        } finally {
            $this->don($luot);
        }
    }

    /** Khoang cach do bang cong thuc duong chim bay, khong phai uoc chung. */
    public function test_do_khoang_cach_giua_hai_tinh(): void
    {
        $hn = NoxhKhoangCach::toaDoTinh('01');
        $this->assertNotNull($hn, 'Ha Noi chua co toa do');

        // Chinh no voi chinh no thi bang 0.
        $this->assertSame(0.0, NoxhKhoangCach::km($hn, $hn));

        // Thieu mot dau thi tra null chu khong tra 0 - 0 nghia la "sat nhau",
        // khac han "khong biet".
        $this->assertNull(NoxhKhoangCach::km($hn, null));
    }

    /** Ba trang ket qua deu mo duoc va dung mau cua muc tuong ung. */
    public function test_ba_muc_ket_qua_deu_ve_dung_khung(): void
    {
        $luot = $this->chay([
            'nhom-doi-tuong' => 'cach-mang',
            'thu-nhap' => 'doc-than-duoi',
            'chinh-sach' => 'chua-ho-tro',
            'nha-o' => 'chua-co-nha',
        ], $this->diaChiCungTinh(), '0900000105');

        try {
            foreach (['high', 'medium', 'low'] as $muc) {
                DB::table('eligibility_checks')->where('id', $luot->id)->update(['result_level' => $muc]);

                $html = $this->get('/kiem-tra-dieu-kien/ket-qua/' . $luot->code)->assertOk()->getContent();

                $this->assertStringContainsString('nx-kq--' . $muc, $html);
                $this->assertStringContainsString('nx-kq-bang', $html);
                $this->assertStringContainsString('nx-kq-diem', $html);

                $dau = DB::table('introduces')->where('keyword', 'result_' . $muc . '_heading')
                    ->where('language_id', 1)->value('content');

                $this->assertNotEmpty($dau);
                $this->assertStringContainsString(e($dau), $html);
            }
        } finally {
            $this->don($luot);
        }
    }

    // -------------------------------------------------------------------------

    private function tieuChi(EligibilityCheck $luot, string $ten): array
    {
        foreach ($luot->tieuChi() as $tc) {
            if (($tc['label'] ?? '') === $ten) {
                return $tc;
            }
        }

        $this->fail("Khong thay tieu chi {$ten} trong ket qua");
    }

    /** Nha va noi lam viec cung mot phuong/xa, du an cung tinh. */
    private function diaChiCungTinh(): array
    {
        $duAn = DB::table('products')->where('publish', 2)->whereNull('deleted_at')
            ->whereNotNull('province_code')->first(['id', 'province_code', 'ward_code']);

        $xa = DB::table('vn_wards')->where('province_code', $duAn->province_code)->value('code');

        return [
            'province_code' => $duAn->province_code,
            'ward_code' => $xa,
            'work_province_code' => $duAn->province_code,
            'work_ward_code' => $xa,
            'project_ids' => [$duAn->id],
        ];
    }

    /** Nha o mot tinh cach xa, noi lam viec cung tinh voi du an. */
    private function diaChiXaNha(): ?array
    {
        $duAn = DB::table('products')->where('publish', 2)->whereNull('deleted_at')
            ->whereNotNull('province_code')->first(['id', 'province_code']);

        $viec = NoxhKhoangCach::toaDoTinh($duAn->province_code);

        // Tim mot tinh cach noi lam viec hon 30km de dat "nha o".
        foreach (DB::table('vn_provinces')->get(['code']) as $t) {
            if ($t->code === $duAn->province_code) {
                continue;
            }

            if ((NoxhKhoangCach::km(NoxhKhoangCach::toaDoTinh($t->code), $viec) ?? 0) > 30) {
                return [
                    'province_code' => $t->code,
                    'ward_code' => DB::table('vn_wards')->where('province_code', $t->code)->value('code'),
                    'work_province_code' => $duAn->province_code,
                    'work_ward_code' => DB::table('vn_wards')->where('province_code', $duAn->province_code)->value('code'),
                    'project_ids' => [$duAn->id],
                ];
            }
        }

        return null;
    }

    /**
     * Di het wizard voi bo cau tra loi cho truoc roi tra ve luot da cham.
     *
     * @param  array<string,string>  $chon  ten buoc => gia tri dap an
     */
    private function chay(array $chon, array $diaChi, string $dienThoai): EligibilityCheck
    {
        $ds = EligibilityQuestion::with('options')
            ->where('publish', 2)->orderBy('order')->orderBy('id')->get()->values();

        $theoBuoc = array_values($chon);

        foreach ($ds as $i => $cau) {
            $so = $i + 1;

            if ($cau->laBuocNhapTin()) {
                break;
            }

            $gui = ['traLoi' => $theoBuoc[$i] ?? ''];

            if ($cau->hoiDiaChi() && $diaChi) {
                $gui += [
                    'province_code' => $diaChi['province_code'],
                    'ward_code' => $diaChi['ward_code'],
                    'work_province_code' => $diaChi['work_province_code'],
                    'work_ward_code' => $diaChi['work_ward_code'],
                    'project_ids' => $diaChi['project_ids'],
                ];
            }

            $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $so, $gui)->assertRedirect();
        }

        $this->post('/kiem-tra-dieu-kien/hoan-tat', [
            'name' => 'Nguoi thu nghiem cham diem',
            'phone' => $dienThoai,
        ])->assertRedirect();

        $luot = EligibilityCheck::where('phone', $dienThoai)->latest('id')->first();
        $this->assertNotNull($luot, 'Khong luu duoc luot kiem tra');

        return $luot;
    }

    private function don(EligibilityCheck $luot): void
    {
        $luot->answers()->delete();
        $luot->delete();
    }
}
