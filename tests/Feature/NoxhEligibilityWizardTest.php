<?php

namespace Tests\Feature;

use App\Models\EligibilityOption;
use App\Models\EligibilityQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Bo kiem tra dieu kien dang wizard (ban ve noxh_image/w-1.jpg).
 *
 * Trong tam: moi cau hoi la MOT buoc, buoc 1 la "Doi tuong" voi luoi o dap an
 * co hinh tron, va toan bo danh muc doi tuong do QUAN TRI them bot - khong co
 * dong nao viet cung trong Blade.
 */
class NoxhEligibilityWizardTest extends TestCase
{
    public function test_buoc_mot_ve_du_cac_khoi_theo_ban_ve(): void
    {
        $html = $this->get('/kiem-tra-dieu-kien/cau-hoi')->assertOk()->getContent();

        foreach (['nx-wz-dai', 'nx-wz-chip', 'nx-wz-buoc', 'nx-wz-the', 'nx-wz-luoi', 'nx-wz-o', 'nx-wz-nut'] as $khoi) {
            $this->assertStringContainsString($khoi, $html, "Thieu khoi {$khoi}");
        }

        $tong = EligibilityQuestion::where('publish', 2)->count();

        $this->assertStringContainsString('Câu 1/' . $tong, $html);

        // Thanh buoc phai in du ten NGAN cua tung cau, khong in ca cau hoi.
        foreach (EligibilityQuestion::where('publish', 2)->orderBy('order')->get() as $ch) {
            $this->assertStringContainsString(e($ch->tenBuoc()), $html);
        }
    }

    /** Buoc 1 phai la cau "Doi tuong" va in du 12 o dap an kem hinh. */
    public function test_buoc_mot_la_cau_doi_tuong_va_in_du_dap_an(): void
    {
        $cau = $this->cauDoiTuong();

        $html = $this->get('/kiem-tra-dieu-kien/cau-hoi/1')->assertOk()->getContent();

        $this->assertStringContainsString(e($cau->question), $html);
        $this->assertStringContainsString(e($cau->hint), $html);
        $this->assertStringContainsString(e($cau->foot_note), $html);

        $this->assertGreaterThanOrEqual(12, $cau->options->count(), 'Ban ve co 12 nhom doi tuong');

        foreach ($cau->options as $da) {
            $this->assertStringContainsString(e($da->label), $html);

            if (trim((string) $da->note) !== '') {
                $this->assertStringContainsString(e($da->note), $html);
            }

            // Hinh di kem tung dap an: tranh cat tu ban ve neu co, khong
            // thi la hinh net trong vong tron mau RIENG cua dap an do.
            if (trim((string) $da->image) !== '') {
                $this->assertStringContainsString(e($da->image), $html);
                continue;
            }

            [$nen] = $da->mauHinh();
            $this->assertStringContainsString($nen, $html);
        }
    }

    /** Go thang duong dan sang giua chung thi bi day ve buoc con thieu. */
    public function test_nhay_coc_sang_buoc_sau_thi_bi_day_ve_buoc_dau(): void
    {
        $this->get('/kiem-tra-dieu-kien/cau-hoi/5')
            ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/1');
    }

    /** Chua chon gi ma bam tiep thi bao loi, khong nhay buoc. */
    public function test_chua_chon_dap_an_thi_khong_di_tiep_duoc(): void
    {
        $this->from('/kiem-tra-dieu-kien/cau-hoi/1')
            ->post('/kiem-tra-dieu-kien/cau-hoi/1', ['traLoi' => ''])
            ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/1')
            ->assertSessionHas('nx_error');
    }

    /** Gia tri la (sua the HTML) khong duoc chui vao phien. */
    public function test_dap_an_khong_co_trong_danh_sach_thi_bi_bo(): void
    {
        $this->post('/kiem-tra-dieu-kien/cau-hoi/1', ['traLoi' => 'ten-bia-dat'])
            ->assertSessionHas('nx_error');
    }

    /** Chon dap an -> sang buoc 2; bam quay lai -> ve buoc 1, dap an van con. */
    public function test_di_toi_roi_lui_lai_thi_dap_an_van_duoc_giu(): void
    {
        $cau = $this->cauDoiTuong();
        $chon = $cau->options->first();

        $this->post('/kiem-tra-dieu-kien/cau-hoi/1', ['traLoi' => $chon->value])
            ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/2');

        $this->post('/kiem-tra-dieu-kien/cau-hoi/2', ['huong' => 'lui'])
            ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/1');

        $html = $this->get('/kiem-tra-dieu-kien/cau-hoi/1')->assertOk()->getContent();

        $this->assertStringContainsString('value="' . e($chon->value) . '" checked', $html);
    }

    /** Buoc dau tien bam quay lai thi ve man hinh dong y. */
    public function test_quay_lai_o_buoc_mot_ve_man_hinh_dong_y(): void
    {
        $this->post('/kiem-tra-dieu-kien/cau-hoi/1', ['huong' => 'lui'])
            ->assertRedirect(route('noxh.check.index'));
    }

    /**
     * Doi mot o chu trong quan tri thi trang doi theo - cach duy nhat chung
     * minh khong con chuoi nao viet cung trong Blade.
     */
    public function test_chu_khung_trang_lay_tu_quan_tri(): void
    {
        $o = [
            'wizard_heading' => 'TIEU DE THU NGHIEM',
            'wizard_chip1_text' => 'The thu nghiem',
            'wizard_next_text' => 'Di tiep thu nghiem',
            'wizard_step_text' => 'Buoc {so} tren {tong}',
        ];

        $cu = DB::table('introduces')->whereIn('keyword', array_keys($o))
            ->where('language_id', 1)->pluck('content', 'keyword')->all();

        foreach ($o as $khoa => $gia) {
            DB::table('introduces')->where('keyword', $khoa)->where('language_id', 1)
                ->update(['content' => $gia]);
        }

        try {
            $html = $this->get('/kiem-tra-dieu-kien/cau-hoi/1')->assertOk()->getContent();

            foreach (['TIEU DE THU NGHIEM', 'The thu nghiem', 'Di tiep thu nghiem'] as $gia) {
                $this->assertStringContainsString($gia, $html);
            }

            $this->assertStringContainsString(
                'Buoc 1 tren ' . EligibilityQuestion::where('publish', 2)->count(),
                $html
            );
        } finally {
            foreach ($cu as $khoa => $gia) {
                DB::table('introduces')->where('keyword', $khoa)->where('language_id', 1)
                    ->update(['content' => $gia]);
            }
        }
    }

    /**
     * Quan tri them mot nhom doi tuong moi thi no hien ngay ngoai trang, xoa
     * di thi bien mat - dung yeu cau "them moi hoac xoa bot cac doi tuong".
     */
    public function test_quan_tri_them_va_xoa_duoc_nhom_doi_tuong(): void
    {
        $quanTri = User::whereHas('user_catalogues.permissions')->where('publish', 2)->first();
        $this->assertNotNull($quanTri, 'Chua co tai khoan quan tri de kiem tra');

        $cau = $this->cauDoiTuong();

        $this->actingAs($quanTri)->get('/eligibility/option/create')->assertOk();

        $this->actingAs($quanTri)->post('/eligibility/option/store', [
            'eligibility_question_id' => $cau->id,
            'label' => 'Nhom thu nghiem tu dong',
            'icon' => 'medal',
            'icon_tone' => 'teal',
            'value' => 'nhom-thu-nghiem-tu-dong',
            'verdict' => 'pass',
            'score' => 3,
            'order' => 99,
            'note' => 'Dong ghi chu thu nghiem',
        ])->assertRedirect(route('eligibility.option.index'));

        $moi = EligibilityOption::where('value', 'nhom-thu-nghiem-tu-dong')->first();
        $this->assertNotNull($moi, 'Khong them duoc nhom doi tuong');
        $this->assertSame('medal', $moi->icon);
        $this->assertSame('teal', $moi->icon_tone);

        try {
            $html = $this->get('/kiem-tra-dieu-kien/cau-hoi/1')->assertOk()->getContent();

            $this->assertStringContainsString('Nhom thu nghiem tu dong', $html);
            $this->assertStringContainsString('Dong ghi chu thu nghiem', $html);
            $this->assertStringContainsString(\App\Classes\NoxhTone::mau('teal')[0], $html);

            // Man hinh sua phai in san hinh va mau da chon.
            $sua = $this->actingAs($quanTri)->get("/eligibility/option/{$moi->id}/edit")
                ->assertOk()->getContent();
            $this->assertStringContainsString('name="icon"', $sua);
            $this->assertStringContainsString('name="icon_tone"', $sua);
        } finally {
            $this->actingAs($quanTri)
                ->delete("/eligibility/option/{$moi->id}/destroy")
                ->assertRedirect(route('eligibility.option.index'));
        }

        $this->assertNull(EligibilityOption::where('value', 'nhom-thu-nghiem-tu-dong')->first());

        $html = $this->get('/kiem-tra-dieu-kien/cau-hoi/1')->assertOk()->getContent();
        $this->assertStringNotContainsString('Nhom thu nghiem tu dong', $html);
    }

    /** Ten buoc va dong luu y sua duoc trong man hinh cau hoi. */
    public function test_ten_buoc_lay_tu_cot_trong_csdl(): void
    {
        $cau = $this->cauDoiTuong();
        $cu = $cau->step_label;

        DB::table('eligibility_questions')->where('id', $cau->id)
            ->update(['step_label' => 'Buoc thu nghiem']);

        try {
            $html = $this->get('/kiem-tra-dieu-kien/cau-hoi/1')->assertOk()->getContent();
            $this->assertStringContainsString('Buoc thu nghiem', $html);
        } finally {
            DB::table('eligibility_questions')->where('id', $cau->id)
                ->update(['step_label' => $cu]);
        }
    }

    private function cauDoiTuong(): EligibilityQuestion
    {
        $cau = EligibilityQuestion::with('options')
            ->where('publish', 2)->orderBy('order')->orderBy('id')->first();

        $this->assertNotNull($cau, 'Chua co cau hoi dieu kien nao');

        return $cau;
    }
}
