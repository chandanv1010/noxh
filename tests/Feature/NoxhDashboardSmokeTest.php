<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Mo thu tat ca man hinh quan tri moi cua NOXH.
 *
 * Khong dung RefreshDatabase: chay tren chinh CSDL dang phat trien de doi
 * chieu voi du lieu that.
 */
class NoxhDashboardSmokeTest extends TestCase
{
    private function quanTri(): User
    {
        return User::orderBy('id')->firstOrFail();
    }

    public static function duongDan(): array
    {
        $ds = [];
        foreach ([
            'investor', 'legal-document', 'expert', 'loan-package',
            'dossier/set', 'dossier/item',
            'eligibility/question', 'eligibility/option',
            'project/milestone', 'project/document', 'project/faq',
            'project/unit', 'project/highlight',
        ] as $p) {
            $ds[$p . ' index'] = [$p . '/index'];
            $ds[$p . ' create'] = [$p . '/create'];
        }
        $ds['eligibility/check index'] = ['eligibility/check/index'];
        $ds['qa/question index'] = ['qa/question/index'];
        $ds['product create'] = ['product/create'];
        return $ds;
    }

    /** @dataProvider duongDan */
    public function test_man_hinh_mo_duoc(string $uri): void
    {
        $this->actingAs($this->quanTri())->get('/' . $uri)->assertOk();
    }

    /**
     * Form SUA du an - khac form them o cho no doc cac quan he da luu
     * (nhan vien phu trach, du an tuong tu), nen phai mo thu rieng.
     */
    public function test_form_sua_du_an_mo_duoc(): void
    {
        $id = \Illuminate\Support\Facades\DB::table('products')
            ->whereNull('deleted_at')->value('id');

        if (!$id) {
            $this->markTestSkipped('Chua co du an nao.');
        }

        $this->actingAs($this->quanTri())->get('/product/' . $id . '/edit')
            ->assertOk()
            ->assertSee('Dự án tương tự', false);
    }
}
