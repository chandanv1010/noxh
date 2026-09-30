<?php

namespace Tests\Feature;

use App\Models\EligibilityOption;
use App\Models\EligibilityOptionGroup;
use App\Models\EligibilityQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Trang mo dau (ban ve start-fix.jpg) va buoc hoi thu nhap chia theo tinh
 * huong (ban ve w-2.jpg).
 *
 * Trong tam: ba tam tinh huong la DU LIEU - quan tri them, sua, xoa duoc -
 * chu khong phai ba khoi viet cung trong Blade.
 */
class NoxhEligibilityMatrixTest extends TestCase
{
    public function test_trang_mo_dau_ve_theo_ban_ve(): void
    {
        $html = $this->get('/kiem-tra-dieu-kien')->assertOk()->getContent();

        foreach (['nx-kt__ten', 'nx-kt__gach', 'nx-kt-gio', 'nx-kt-bm', 'nx-kt-nut', 'nx-kt-luu'] as $khoi) {
            $this->assertStringContainsString($khoi, $html, "Thieu khoi {$khoi}");
        }

        // Tranh nen va hai hinh tron deu cat tu ban ve, khong ve bang CSS.
        foreach (['start_bg', 'start_image', 'start_privacy_image'] as $khoa) {
            $duong = DB::table('introduces')->where('keyword', $khoa)
                ->where('language_id', 1)->value('content');

            $this->assertNotEmpty($duong, "O {$khoa} dang trong");
            $this->assertStringContainsString($duong, $html);
            $this->assertFileExists(public_path(ltrim($duong, '/')));
        }
    }

    /** Doi mot o chu trong quan tri thi trang mo dau doi theo. */
    public function test_chu_trang_mo_dau_lay_tu_quan_tri(): void
    {
        $o = [
            'start_heading' => 'TIEU DE MO DAU THU NGHIEM',
            'start_button' => 'Nut thu nghiem',
            'start_time_strong' => '9 phut thu nghiem',
        ];

        $cu = DB::table('introduces')->whereIn('keyword', array_keys($o))
            ->where('language_id', 1)->pluck('content', 'keyword')->all();

        foreach ($o as $khoa => $gia) {
            DB::table('introduces')->where('keyword', $khoa)->where('language_id', 1)
                ->update(['content' => $gia]);
        }

        try {
            $html = $this->get('/kiem-tra-dieu-kien')->assertOk()->getContent();

            foreach ($o as $gia) {
                $this->assertStringContainsString($gia, $html);
            }
        } finally {
            foreach ($cu as $khoa => $gia) {
                DB::table('introduces')->where('keyword', $khoa)->where('language_id', 1)
                    ->update(['content' => $gia]);
            }
        }
    }

    /** Buoc chia theo tinh huong in du ba tam kem dap an cua tung tam. */
    public function test_buoc_thu_nhap_ve_du_ba_tam(): void
    {
        [$buoc, $cau] = $this->buocMatrix();

        $html = $this->diToi($buoc);

        $this->assertStringContainsString('nx-wz-tam', $html);
        $this->assertGreaterThanOrEqual(3, $cau->optionGroups->count(), 'Ban ve co ba tinh huong');

        foreach ($cau->optionGroups as $tam) {
            $this->assertStringContainsString(e($tam->label), $html);

            if (trim((string) $tam->note) !== '') {
                $this->assertStringContainsString(e($tam->note), $html);
            }

            $this->assertGreaterThan(0, $tam->options->count(), "Tam {$tam->label} chua co dap an");

            foreach ($tam->options as $da) {
                $this->assertStringContainsString(e($da->label), $html);
            }
        }

        // Dai luu y hai dong.
        $this->assertStringContainsString(e($cau->foot_note), $html);
        $this->assertStringContainsString(e($cau->foot_note_sub), $html);

        // Tu buoc 2 tro di ban ve bo dai gioi thieu, dua thanh buoc vao the.
        $this->assertStringNotContainsString('nx-wz-dai', $html);
        $this->assertStringContainsString('nx-wz-buoc--trong', $html);
    }

    /** Ca ba tam dung CHUNG mot nhom radio - chi chon duoc mot muc. */
    public function test_ba_tam_dung_chung_mot_nhom_radio(): void
    {
        [$buoc] = $this->buocMatrix();

        $html = $this->diToi($buoc);

        $so = substr_count($html, 'name="traLoi"');
        $this->assertGreaterThanOrEqual(6, $so, 'Ban ve co sau muc thu nhap');
        $this->assertSame(0, substr_count($html, 'name="traLoi["'), 'Khong duoc tach thanh nhieu nhom');
    }

    /**
     * Quan tri them mot tinh huong moi thi no hien ngay ngoai trang, xoa di
     * thi tam bien mat nhung DAP AN trong tam khong bi xoa theo.
     */
    public function test_quan_tri_them_va_xoa_duoc_tinh_huong(): void
    {
        $quanTri = User::whereHas('user_catalogues.permissions')->where('publish', 2)->first();
        $this->assertNotNull($quanTri, 'Chua co tai khoan quan tri de kiem tra');

        [$buoc, $cau] = $this->buocMatrix();

        $this->actingAs($quanTri)->get('/eligibility/group/index')->assertOk();
        $this->actingAs($quanTri)->get('/eligibility/group/create')->assertOk();

        $this->actingAs($quanTri)->post('/eligibility/group/store', [
            'eligibility_question_id' => $cau->id,
            'label' => 'Tinh huong thu nghiem',
            'note' => '(dong ghi chu thu nghiem)',
            'icon' => 'user',
            'tone' => 'teal',
            'order' => 90,
        ])->assertRedirect(route('eligibility.group.index'));

        $tam = EligibilityOptionGroup::where('label', 'Tinh huong thu nghiem')->first();
        $this->assertNotNull($tam, 'Khong them duoc tinh huong');

        $this->actingAs($quanTri)->post('/eligibility/option/store', [
            'eligibility_question_id' => $cau->id,
            'eligibility_option_group_id' => $tam->id,
            'label' => 'Muc thu nghiem',
            'value' => 'muc-thu-nghiem',
            'verdict' => 'pass',
            'score' => 1,
            'order' => 99,
        ])->assertRedirect(route('eligibility.option.index'));

        $dapAn = EligibilityOption::where('value', 'muc-thu-nghiem')->first();
        $this->assertNotNull($dapAn);

        try {
            $html = $this->diToi($buoc);

            $this->assertStringContainsString('Tinh huong thu nghiem', $html);
            $this->assertStringContainsString('(dong ghi chu thu nghiem)', $html);
            $this->assertStringContainsString('Muc thu nghiem', $html);
        } finally {
            $this->actingAs($quanTri)
                ->delete("/eligibility/group/{$tam->id}/destroy")
                ->assertRedirect(route('eligibility.group.index'));
        }

        // Xoa tam thi dap an CON NGUYEN, chi bi go khoi tam.
        $sau = EligibilityOption::find($dapAn->id);
        $this->assertNotNull($sau, 'Xoa tam khong duoc xoa lay dap an');
        $this->assertNull($sau->eligibility_option_group_id);

        $sau->delete();

        $this->assertNull(EligibilityOptionGroup::find($tam->id));
    }

    // -------------------------------------------------------------------------

    /** [so buoc, cau hoi] cua cau dat bo cuc "chia theo tinh huong". */
    private function buocMatrix(): array
    {
        $ds = EligibilityQuestion::with(['options', 'optionGroups.options'])
            ->where('publish', 2)->orderBy('order')->orderBy('id')->get()->values();

        foreach ($ds as $i => $ch) {
            if ($ch->laMatrix()) {
                return [$i + 1, $ch];
            }
        }

        $this->markTestSkipped('Chua co cau hoi nao dat bo cuc chia theo tinh huong.');
    }

    /** Tra loi lan luot cac buoc truoc roi mo buoc can xem. */
    private function diToi(int $buoc): string
    {
        $ds = EligibilityQuestion::with('options')
            ->where('publish', 2)->orderBy('order')->orderBy('id')->get()->values();

        for ($i = 1; $i < $buoc; $i++) {
            $dapAn = $ds[$i - 1]->options->first();

            $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $i, [
                'traLoi' => $dapAn->value ?? '',
            ])->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/' . ($i + 1));
        }

        return $this->get('/kiem-tra-dieu-kien/cau-hoi/' . $buoc)->assertOk()->getContent();
    }
}
