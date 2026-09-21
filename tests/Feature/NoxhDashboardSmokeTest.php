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
}
