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
use App\Models\ProjectHighlight;
use App\Models\ProjectUnit;
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

    public function test_them_sua_xoa_loai_can_ho(): void
    {
        $u = $this->quanTri();
        $duAnId = \Illuminate\Support\Facades\DB::table('products')
            ->whereNull('deleted_at')->value('id');

        if (!$duAnId) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $this->actingAs($u)->post('/project/unit/store', [
            'product_id' => $duAnId,
            'name' => 'Can thu nghiem tu dong',
            'area_from' => 19.55,
            'area_to' => 21,
            'price_from' => 1.075,
            'price_to' => 1.18,
            'price_unit' => 'tỷ',
            'bullets' => "Gach mot\nGach hai",
            'publish' => 2,
            'order' => 0,
        ])->assertRedirect(route('project.unit.index'));

        $o = ProjectUnit::where('name', 'Can thu nghiem tu dong')->first();
        $this->assertNotNull($o, 'Khong luu duoc loai can ho');
        $this->assertSame(['Gach mot', 'Gach hai'], $o->diem);

        $this->actingAs($u)->post("/project/unit/{$o->id}/update", [
            'product_id' => $duAnId,
            'name' => 'Can da doi ten',
            'publish' => 1,
            'order' => 3,
        ])->assertRedirect(route('project.unit.index'));

        $o->refresh();
        $this->assertSame('Can da doi ten', $o->name);
        // O so bo trong phai thanh NULL chu khong phai 0.
        $this->assertNull($o->area_from);

        $this->actingAs($u)->delete("/project/unit/{$o->id}/destroy")
            ->assertRedirect(route('project.unit.index'));

        $this->assertNull(ProjectUnit::find($o->id));
    }

    public function test_dien_tich_den_nho_hon_tu_thi_bi_chan(): void
    {
        $u = $this->quanTri();
        $duAnId = \Illuminate\Support\Facades\DB::table('products')
            ->whereNull('deleted_at')->value('id');

        if (!$duAnId) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $this->actingAs($u)
            ->from('/project/unit/create')
            ->post('/project/unit/store', [
                'product_id' => $duAnId,
                'name' => 'Can khoang nguoc',
                'area_from' => 50,
                'area_to' => 30,
            ])
            ->assertSessionHasErrors('area_to');

        $this->assertNull(ProjectUnit::where('name', 'Can khoang nguoc')->first());
    }

    public function test_them_sua_xoa_diem_nhan(): void
    {
        $u = $this->quanTri();
        $duAnId = \Illuminate\Support\Facades\DB::table('products')
            ->whereNull('deleted_at')->value('id');

        if (!$duAnId) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $this->actingAs($u)->post('/project/highlight/store', [
            'product_id' => $duAnId,
            'group' => 'amenity',
            'icon' => 'verified',
            'title' => 'Diem nhan thu nghiem',
            'subtitle' => 'dong duoi',
            'order' => 0,
        ])->assertRedirect(route('project.highlight.index'));

        $o = ProjectHighlight::where('title', 'Diem nhan thu nghiem')->first();
        $this->assertNotNull($o, 'Khong luu duoc diem nhan');
        $this->assertSame('amenity', $o->group);

        // Nhom la thu quyet dinh o nay hien o KHOI NAO, gui bua thi phai bi
        // chan chu khong duoc am tham nhet vao khoi khac.
        $this->actingAs($u)
            ->from("/project/highlight/{$o->id}/edit")
            ->post("/project/highlight/{$o->id}/update", [
                'product_id' => $duAnId,
                'group' => 'linh tinh',
                'title' => 'Diem nhan thu nghiem',
            ])
            ->assertSessionHasErrors('group');

        $this->actingAs($u)->delete("/project/highlight/{$o->id}/destroy")
            ->assertRedirect(route('project.highlight.index'));

        $this->assertNull(ProjectHighlight::find($o->id));
    }

    public function test_giay_to_phai_khai_thuoc_khoi_nao(): void
    {
        $u = $this->quanTri();
        $duAnId = \Illuminate\Support\Facades\DB::table('products')
            ->whereNull('deleted_at')->value('id');

        if (!$duAnId) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        // Nhom quyet dinh giay to hien o tab "Phap ly" hay tab "Tai lieu".
        // Gui bua thi phai bi chan chu khong duoc am tham nhet vao mot khoi.
        $this->actingAs($u)
            ->from('/project/document/create')
            ->post('/project/document/store', [
                'product_id' => $duAnId,
                'group' => 'linh tinh',
                'title' => 'Giay to nhom bua',
            ])
            ->assertSessionHasErrors('group');

        $this->assertNull(
            \App\Models\ProjectDocument::where('title', 'Giay to nhom bua')->first()
        );

        $this->actingAs($u)->post('/project/document/store', [
            'product_id' => $duAnId,
            'group' => 'doc',
            'title' => 'Tai lieu thu nghiem tu dong',
            'publish' => 2,
            'order' => 0,
        ])->assertRedirect(route('project.document.index'));

        $o = \App\Models\ProjectDocument::where('title', 'Tai lieu thu nghiem tu dong')->first();
        $this->assertNotNull($o);
        $this->assertSame('doc', $o->group);

        $this->actingAs($u)->delete("/project/document/{$o->id}/destroy")
            ->assertRedirect(route('project.document.index'));
    }
}
