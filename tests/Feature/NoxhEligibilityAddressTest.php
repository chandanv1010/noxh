<?php

namespace Tests\Feature;

use App\Models\EligibilityCheck;
use App\Models\EligibilityQuestion;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Khoi dia chi nha o / noi lam viec va danh sach du an quan tam
 * (nua duoi ban ve noxh_image/w-4.jpg).
 *
 * Trong tam: hai duong dan JSON tra dung du lieu, va ma tinh / xa / du an
 * la KHONG chui vao ket qua duoc - nguoi dung sua the HTML thi may chu phai
 * loai ra.
 */
class NoxhEligibilityAddressTest extends TestCase
{
    public function test_buoc_nha_o_in_khoi_dia_chi(): void
    {
        [$so] = $this->buocDiaChi();

        $html = $this->diToi($so);

        $this->assertStringContainsString('data-nx-dia-chi', $html);
        $this->assertStringContainsString('name="province_code"', $html);
        $this->assertStringContainsString('name="work_province_code"', $html);
        $this->assertStringContainsString('nx-wz-da', $html);

        // Danh sach tinh in san trong trang, phuong/xa nap sau theo tinh.
        $tinh = DB::table('vn_provinces')->orderBy('order')->first();
        $this->assertStringContainsString(e($tinh->name), $html);
    }

    public function test_duong_dan_json_tra_phuong_xa_cua_dung_tinh(): void
    {
        $tinh = DB::table('vn_wards')->select('province_code')
            ->groupBy('province_code')->orderByRaw('COUNT(*) DESC')->value('province_code');

        $ra = $this->get('/kiem-tra-dieu-kien/phuong-xa/' . $tinh)->assertOk()->json();

        $this->assertNotEmpty($ra);

        $ma = array_column($ra, 'code');
        $that = DB::table('vn_wards')->where('province_code', $tinh)->pluck('code')->all();

        sort($ma);
        sort($that);
        $this->assertSame($that, $ma);
    }

    public function test_duong_dan_json_tra_du_an_cua_dung_tinh(): void
    {
        $tinh = DB::table('products')->where('publish', 2)->whereNull('deleted_at')
            ->whereNotNull('province_code')->value('province_code');

        $this->assertNotNull($tinh, 'Chua co du an nao gan tinh');

        $ra = $this->get('/kiem-tra-dieu-kien/du-an/' . $tinh)->assertOk()->json();

        $this->assertNotEmpty($ra['duAn']);
        $this->assertNotEmpty($ra['noi']);

        $cuaTinh = DB::table('products')->where('publish', 2)->whereNull('deleted_at')
            ->where('province_code', $tinh)->pluck('id')->all();

        foreach ($ra['duAn'] as $d) {
            $this->assertContains($d['id'], $cuaTinh, 'Lot du an cua tinh khac vao danh sach');
        }
    }

    /**
     * Ma dia gioi la va bi bo, du an khong co that cung bi bo.
     *
     * Day la cho chan THAT: JS o trinh duyet chan duoc thi tot, nhung ai go
     * thang mot yeu cau khac van phai bi loai.
     */
    public function test_ma_dia_gioi_la_thi_bi_bo(): void
    {
        [$so] = $this->buocDiaChi();
        $this->diToi($so);

        $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $so, [
            'traLoi' => $this->dapAnDau($so),
            'province_code' => '9999',
            'ward_code' => '99999',
            'work_province_code' => '9999',
            'work_ward_code' => '99999',
            'project_ids' => [999999],
        ])->assertRedirect();

        $this->post('/kiem-tra-dieu-kien/hoan-tat', [
            'name' => 'Nguoi thu nghiem dia chi la',
            'phone' => '0900000088',
        ])->assertRedirect();

        $luot = EligibilityCheck::where('phone', '0900000088')->latest('id')->first();
        $this->assertNotNull($luot);

        try {
            $this->assertNull($luot->ward_code);
            $this->assertNull($luot->work_province_code);
            $this->assertNull($luot->project_ids);
        } finally {
            $luot->answers()->delete();
            $luot->delete();
        }
    }

    /** Dia chi that va du an that thi duoc luu lai cung luot kiem tra. */
    public function test_dia_chi_that_duoc_luu_vao_luot_kiem_tra(): void
    {
        [$so] = $this->buocDiaChi();
        $this->diToi($so);

        $xa = DB::table('vn_wards')->first();
        $duAn = DB::table('products')->where('publish', 2)->whereNull('deleted_at')->value('id');

        $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $so, [
            'traLoi' => $this->dapAnDau($so),
            'province_code' => $xa->province_code,
            'ward_code' => $xa->code,
            'work_province_code' => $xa->province_code,
            'work_ward_code' => $xa->code,
            'project_ids' => [$duAn],
        ])->assertRedirect();

        $this->post('/kiem-tra-dieu-kien/hoan-tat', [
            'name' => 'Nguoi thu nghiem dia chi that',
            'phone' => '0900000089',
        ])->assertRedirect();

        $luot = EligibilityCheck::where('phone', '0900000089')->latest('id')->first();
        $this->assertNotNull($luot);

        try {
            $this->assertSame($xa->province_code, $luot->province_code);
            $this->assertSame($xa->code, $luot->ward_code);
            $this->assertSame($xa->province_code, $luot->work_province_code);
            $this->assertSame((string) $duAn, (string) $luot->project_ids);
        } finally {
            $luot->answers()->delete();
            $luot->delete();
        }
    }

    // -------------------------------------------------------------------------

    /** [so buoc, cau hoi] cua buoc co hoi them dia chi. */
    private function buocDiaChi(): array
    {
        $ds = $this->buoc();

        foreach ($ds as $i => $cau) {
            if ($cau->hoiDiaChi()) {
                return [$i + 1, $cau];
            }
        }

        $this->markTestSkipped('Chua buoc nao bat khoi dia chi.');
    }

    private function buoc()
    {
        return EligibilityQuestion::with('options')
            ->where('publish', 2)->orderBy('order')->orderBy('id')->get()->values();
    }

    private function dapAnDau(int $so): string
    {
        return (string) ($this->buoc()[$so - 1]->options->first()->value ?? '');
    }

    /** Tra loi lan luot cac buoc truoc roi mo buoc can xem. */
    private function diToi(int $buoc): string
    {
        $ds = $this->buoc();

        for ($i = 1; $i < $buoc; $i++) {
            $this->post('/kiem-tra-dieu-kien/cau-hoi/' . $i, ['traLoi' => $this->dapAnDau($i)])
                ->assertRedirect('/kiem-tra-dieu-kien/cau-hoi/' . ($i + 1));
        }

        return $this->get('/kiem-tra-dieu-kien/cau-hoi/' . $buoc)->assertOk()->getContent();
    }
}
