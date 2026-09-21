<?php

namespace Tests\Feature;

use App\Models\DossierItem;
use App\Models\DossierSet;
use App\Models\EligibilityOption;
use App\Models\EligibilityQuestion;
use App\Models\Expert;
use App\Models\Investor;
use App\Models\LegalDocument;
use App\Models\LoanPackage;
use App\Models\User;
use Tests\TestCase;

/**
 * Thu them - sua - xoa that tren cac module quan tri moi cua NOXH.
 *
 * Khong dung RefreshDatabase: chay tren chinh CSDL dang phat trien. Ban ghi
 * nao tao ra trong test deu tu don o cuoi moi ham.
 */
class NoxhDashboardCrudTest extends TestCase
{
    private function quanTri(): User
    {
        return User::orderBy('id')->firstOrFail();
    }

    public function test_them_sua_xoa_chu_dau_tu(): void
    {
        $u = $this->quanTri();

        $this->actingAs($u)->post('/investor/store', [
            'name' => 'Cong ty thu nghiem tu dong',
            'short_name' => 'TNTD',
            'hotline' => '1900 0000',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('investor.index'));

        $o = Investor::where('name', 'Cong ty thu nghiem tu dong')->first();
        $this->assertNotNull($o, 'Khong luu duoc chu dau tu');
        $this->assertSame('TNTD', $o->short_name);

        $this->actingAs($u)->post("/investor/{$o->id}/update", [
            'name' => 'Cong ty da doi ten',
            'order' => 5,
            'publish' => 1,
        ])->assertRedirect(route('investor.index'));

        $o->refresh();
        $this->assertSame('Cong ty da doi ten', $o->name);
        $this->assertSame(1, (int) $o->publish);

        $this->actingAs($u)->delete("/investor/{$o->id}/destroy")
            ->assertRedirect(route('investor.index'));

        $this->assertNull(Investor::find($o->id));
        $o->forceDelete();
    }

    public function test_van_ban_phap_luat_luu_dung_ngay_va_o_tich(): void
    {
        $u = $this->quanTri();

        $this->actingAs($u)->post('/legal-document/store', [
            'title' => 'Nghi dinh thu nghiem tu dong',
            'doc_number' => '999/2026/ND-CP',
            'doc_type' => 'nghi_dinh',
            'effective_date' => '2026-01-15',
            'is_featured' => '1',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('legal.document.index'));

        $o = LegalDocument::where('doc_number', '999/2026/ND-CP')->firstOrFail();
        $this->assertSame('2026-01-15', $o->effective_date->format('Y-m-d'));
        $this->assertTrue($o->is_featured);

        // Bo tich thi trinh duyet khong gui o do len - phai tu dat ve 0.
        $this->actingAs($u)->post("/legal-document/{$o->id}/update", [
            'title' => 'Nghi dinh thu nghiem tu dong',
            'doc_type' => 'nghi_dinh',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('legal.document.index'));

        $o->refresh();
        $this->assertFalse($o->is_featured, 'Bo tich "noi bat" nhung van con bat');
        $this->assertNull($o->effective_date, 'Xoa ngay nhung van con gia tri cu');

        $o->forceDelete();
    }

    public function test_goi_vay_luu_duoc_so_thap_phan_va_de_trong_ra_null(): void
    {
        $u = $this->quanTri();

        $this->actingAs($u)->post('/loan-package/store', [
            'bank_name' => 'Ngan hang thu nghiem',
            'package_name' => 'Goi NOXH 2026',
            'preferential_rate' => '4.8',
            'preferential_months' => '60',
            'standard_rate' => '9.5',
            'max_loan_ratio' => '80',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('loan.package.index'));

        $o = LoanPackage::where('bank_name', 'Ngan hang thu nghiem')->firstOrFail();
        $this->assertSame('4.80', (string) $o->preferential_rate);
        $this->assertSame(60, (int) $o->preferential_months);

        // O so de trong phai ra null chu khong phai 0: lai suat 0% se lam cong
        // cu tinh khoan vay ra so tien tra hang thang bang khong.
        $this->actingAs($u)->post("/loan-package/{$o->id}/update", [
            'bank_name' => 'Ngan hang thu nghiem',
            'preferential_rate' => '',
            'standard_rate' => '',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('loan.package.index'));

        $o->refresh();
        $this->assertNull($o->preferential_rate);
        $this->assertNull($o->standard_rate);

        $o->forceDelete();
    }

    public function test_bo_ho_so_va_giay_to_di_kem(): void
    {
        $u = $this->quanTri();

        $this->actingAs($u)->post('/dossier/set/store', [
            'name' => 'Bo ho so thu nghiem',
            'subject_group' => 'Cong nhan',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('dossier.set.index'));

        $bo = DossierSet::where('name', 'Bo ho so thu nghiem')->firstOrFail();

        $this->actingAs($u)->post('/dossier/item/store', [
            'dossier_set_id' => $bo->id,
            'title' => 'Giay to thu nghiem',
            'copies' => '2',
            'is_required' => '1',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('dossier.item.index'));

        $giay = DossierItem::where('title', 'Giay to thu nghiem')->firstOrFail();
        $this->assertSame($bo->id, (int) $giay->dossier_set_id);
        $this->assertSame(2, (int) $giay->copies);

        // Loc theo bo ho so phai chi ra dung giay to cua bo do.
        $this->actingAs($u)->get('/dossier/item/index?dossier_set_id=' . $bo->id)
            ->assertOk()
            ->assertSee('Giay to thu nghiem');

        $giay->delete();
        $bo->forceDelete();
    }

    public function test_cau_hoi_dieu_kien_va_dap_an(): void
    {
        $u = $this->quanTri();

        $this->actingAs($u)->post('/eligibility/question/store', [
            'question' => 'Cau hoi thu nghiem tu dong?',
            'group' => 'income',
            'input_type' => 'select',
            'weight' => '3',
            'required' => '1',
            'order' => 99,
            'publish' => 2,
        ])->assertRedirect(route('eligibility.question.index'));

        $ch = EligibilityQuestion::where('question', 'Cau hoi thu nghiem tu dong?')->firstOrFail();
        $this->assertSame('income', $ch->group);
        $this->assertSame(3, (int) $ch->weight);

        $this->actingAs($u)->post('/eligibility/option/store', [
            'eligibility_question_id' => $ch->id,
            'label' => 'Duoi 15 trieu',
            'value' => 'duoi_15',
            'verdict' => 'pass',
            'score' => '10',
            'order' => 0,
        ])->assertRedirect(route('eligibility.option.index'));

        $da = EligibilityOption::where('value', 'duoi_15')->firstOrFail();
        $this->assertSame($ch->id, (int) $da->eligibility_question_id);
        $this->assertSame(10, (int) $da->score);
        $this->assertSame('pass', $da->verdict);

        $da->delete();
        $ch->forceDelete();
    }

    public function test_chuyen_gia_mac_dinh(): void
    {
        $u = $this->quanTri();

        $this->actingAs($u)->post('/expert/store', [
            'name' => 'Chuyen gia thu nghiem',
            'title' => 'Co van phap ly',
            'phone' => '0900000000',
            'is_default' => '1',
            'order' => 0,
            'publish' => 2,
        ])->assertRedirect(route('expert.index'));

        $o = Expert::where('name', 'Chuyen gia thu nghiem')->firstOrFail();
        $this->assertTrue($o->is_default);

        $o->forceDelete();
    }

    public function test_bo_trong_o_bat_buoc_thi_bao_loi(): void
    {
        $this->actingAs($this->quanTri())
            ->post('/investor/store', ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertNull(Investor::where('name', '')->first());
    }
}
