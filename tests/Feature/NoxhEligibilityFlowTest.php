<?php

namespace Tests\Feature;

use App\Models\EligibilityCheck;
use App\Models\EligibilityOption;
use App\Models\EligibilityPanel;
use App\Models\EligibilityQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Duong di nam buoc va luat "dung som" (ban ve w-1 .. w-5).
 *
 * Trong tam: chon dap an co danh dau dung som thi BO QUA cac buoc con lai,
 * di thang toi buoc nhap thong tin - va buoc bi bo qua khong bi tinh la
 * "chua tra loi" khi cham diem.
 */
class NoxhEligibilityFlowTest extends TestCase
{
    public function test_bo_cau_hoi_dung_nam_buoc(): void
    {
        $ds = $this->buoc();

        $this->assertCount(5, $ds, 'Ban ve w-1..w-5 chi co nam buoc');

        // Buoc cuoi la o nhap thong tin, khong phai cau hoi.
        $this->assertTrue($ds->last()->laBuocNhapTin());

        foreach ($ds->take(4) as $cau) {
            $this->assertGreaterThan(0, $cau->options->count() + $cau->optionGroups->count(),
                "Buoc {$cau->tenBuoc()} chua co dap an nao");
        }
    }

    /** Moi buoc mot bo cuc rieng, dung theo tung ban ve. */
    public function test_moi_buoc_dung_bo_cuc_cua_ban_ve(): void
    {
        $mong = ['grid', 'matrix', 'list', 'card', 'contact'];

        $this->assertSame($mong, $this->buoc()->pluck('layout')->all());
    }

    /**
     * Chon "Da tung duoc ho tro" o buoc Chinh sach: bo qua buoc Nha o.
     */
    public function test_dap_an_dung_som_thi_bo_qua_buoc_con_lai(): void
    {
        $ds = $this->buoc();
        $dung = $this->dapAnDungSom();

        $this->assertNotNull($dung, 'Chua co dap an nao danh dau dung som');

        $soBuocDung = $ds->search(fn ($c) => $c->id === $dung->eligibility_question_id) + 1;
        $this->assertGreaterThan(1, $soBuocDung);

        // Tra loi cac buoc truoc cho toi buoc co dap an dung som.
        for ($i = 1; $i < $soBuocDung; $i++) {
            $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $i, ['traLoi' => $this->dapAnDau($ds[$i - 1])])
                ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/' . ($i + 1));
        }

        // Chon dap an dung som -> nhay thang toi buoc cuoi.
        $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $soBuocDung, ['traLoi' => $dung->value])
            ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/' . $ds->count());

        // Go thang duong dan cua buoc bi bo qua thi bi day ve buoc cuoi.
        if ($soBuocDung + 1 < $ds->count()) {
            $this->get('/kiem-tra-dieu-kien/cau-hoi/' . ($soBuocDung + 1))
                ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/' . $ds->count());
        }

        // Thanh buoc phai lam mo buoc bi bo qua.
        $html = $this->get('/kiem-tra-dieu-kien/cau-hoi/' . $ds->count())->assertOk()->getContent();
        $this->assertStringContainsString('bo-qua', $html);
    }

    /** Doi sang dap an khong dung som thi buoc bi bo qua hien lai. */
    public function test_doi_lai_dap_an_thi_buoc_bi_bo_qua_hien_lai(): void
    {
        $ds = $this->buoc();
        $dung = $this->dapAnDungSom();
        $this->assertNotNull($dung);

        $soBuocDung = $ds->search(fn ($c) => $c->id === $dung->eligibility_question_id) + 1;

        for ($i = 1; $i < $soBuocDung; $i++) {
            $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $i, ['traLoi' => $this->dapAnDau($ds[$i - 1])]);
        }

        $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $soBuocDung, ['traLoi' => $dung->value])
            ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/' . $ds->count());

        // Quay lai doi sang dap an thuong -> buoc ke tiep tro lai binh thuong.
        $thuong = $ds[$soBuocDung - 1]->options->firstWhere('stop_flow', false);
        $this->assertNotNull($thuong);

        $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $soBuocDung, ['traLoi' => $thuong->value])
            ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/' . ($soBuocDung + 1));
    }

    /**
     * Buoc bi bo qua KHONG bi tinh diem.
     *
     * Neu tinh, nguoi dung mat diem vi mot cau khong ai hoi ho - va con so
     * "so cau" tren trang ket qua se nhieu hon so cau ho thay.
     */
    public function test_buoc_bi_bo_qua_khong_bi_cham_diem(): void
    {
        $ds = $this->buoc();
        $dung = $this->dapAnDungSom();
        $this->assertNotNull($dung);

        $soBuocDung = $ds->search(fn ($c) => $c->id === $dung->eligibility_question_id) + 1;

        for ($i = 1; $i < $soBuocDung; $i++) {
            $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $i, ['traLoi' => $this->dapAnDau($ds[$i - 1])]);
        }

        $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $soBuocDung, ['traLoi' => $dung->value]);

        $this->post('/kiem-tra-dieu-kien/hoan-tat', [
            'name' => 'Nguoi thu nghiem dung som',
            'phone' => '0900000077',
        ])->assertRedirect();

        $luot = EligibilityCheck::where('phone', '0900000077')->latest('id')->first();
        $this->assertNotNull($luot);

        try {
            // Chi dem nhung buoc that su di qua: khong co buoc nhap tin,
            // khong co buoc bi bo qua.
            $this->assertSame($soBuocDung, (int) $luot->total_questions);
            $this->assertSame($soBuocDung, $luot->answers()->count());
            $this->assertSame('low', $luot->result_level, 'Da tung duoc ho tro thi khong the dat muc cao');
        } finally {
            $luot->answers()->delete();
            $luot->delete();
        }
    }

    /** Cot phai cua ba buoc cuoi lay tu bang, khong viet cung trong Blade. */
    public function test_tam_cot_phai_lay_tu_csdl(): void
    {
        $ds = $this->buoc();
        $coTam = $ds->first(fn ($c) => $c->panels->count() > 0);

        $this->assertNotNull($coTam, 'Chua buoc nao co tam cot phai');

        $so = $ds->search(fn ($c) => $c->id === $coTam->id) + 1;

        for ($i = 1; $i < $so; $i++) {
            $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $i, ['traLoi' => $this->dapAnDau($ds[$i - 1])]);
        }

        $html = $this->get('/kiem-tra-dieu-kien/cau-hoi/' . $so)->assertOk()->getContent();

        foreach ($coTam->panels as $tam) {
            if (trim((string) $tam->heading) !== '') {
                $this->assertStringContainsString(e($tam->heading), $html);
            }

            foreach ($tam->dongY() as $dong) {
                $this->assertStringContainsString(e($dong), $html);
            }
        }
    }

    /** Quan tri them va xoa duoc tam cot phai. */
    public function test_quan_tri_them_va_xoa_duoc_tam_cot_phai(): void
    {
        $quanTri = User::whereHas('user_catalogues.permissions')->where('publish', 2)->first();
        $this->assertNotNull($quanTri, 'Chua co tai khoan quan tri de kiem tra');

        $cau = $this->buoc()->first();

        $this->actingAs($quanTri)->get('/eligibility/panel/index')->assertOk();
        $this->actingAs($quanTri)->get('/eligibility/panel/create')->assertOk();

        $this->actingAs($quanTri)->post('/eligibility/panel/store', [
            'eligibility_question_id' => $cau->id,
            'heading' => 'Tam thu nghiem tu dong',
            'bullets' => "Y thu nghiem mot\nY thu nghiem hai",
            'icon' => 'info',
            'tone' => 'amber',
            'order' => 90,
        ])->assertRedirect(route('eligibility.panel.index'));

        $tam = EligibilityPanel::where('heading', 'Tam thu nghiem tu dong')->first();
        $this->assertNotNull($tam);
        $this->assertSame(['Y thu nghiem mot', 'Y thu nghiem hai'], $tam->dongY());

        try {
            $html = $this->get('/kiem-tra-dieu-kien/cau-hoi/1')->assertOk()->getContent();
            $this->assertStringContainsString('Tam thu nghiem tu dong', $html);
            $this->assertStringContainsString('Y thu nghiem hai', $html);
        } finally {
            $this->actingAs($quanTri)->delete("/eligibility/panel/{$tam->id}/destroy")
                ->assertRedirect(route('eligibility.panel.index'));
        }

        $this->assertNull(EligibilityPanel::find($tam->id));
    }

    // -------------------------------------------------------------------------

    private function buoc()
    {
        return EligibilityQuestion::with(['options', 'optionGroups.options', 'panels'])
            ->where('publish', 2)->orderBy('order')->orderBy('id')->get()->values();
    }

    private function dapAnDungSom(): ?EligibilityOption
    {
        return EligibilityOption::where('stop_flow', true)
            ->whereIn('eligibility_question_id', DB::table('eligibility_questions')->where('publish', 2)->pluck('id'))
            ->first();
    }

    /** Gia tri cua dap an dau tien cua mot buoc (ke ca khi nam trong tam). */
    private function dapAnDau($cau): string
    {
        return (string) ($cau->options->first()->value ?? '');
    }
}
