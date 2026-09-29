<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Trang danh sach du an, dung lai theo ban thiet ke
 * noxh_image/project-cate-fix.jpg.
 *
 * Trong tam la nhung thu de vo am tham: bon o so lieu doc tu quan tri va
 * thay so that vao dau {du_an}/{tinh}, khoi ban do voi ghim chieu tu toa do
 * trong vn_provinces, va trang ban do rieng.
 *
 * Khong dung RefreshDatabase - chay tren CSDL dang phat trien, giong cac
 * test frontend khac.
 */
class NoxhProjectListingTest extends TestCase
{
    public function test_dai_bon_o_so_lieu_lay_tu_quan_tri(): void
    {
        $html = $this->get('/du-an')->assertOk()->getContent();

        foreach (['Dự án toàn quốc', 'Tỉnh / Thành phố', 'Khách hàng quan tâm', 'Thông tin kiểm chứng'] as $nhan) {
            $this->assertStringContainsString($nhan, $html, "Thieu o so lieu \"{$nhan}\"");
        }

        // Dau thay the phai duoc thay bang so that, khong duoc lot ra trang.
        $this->assertStringNotContainsString('{du_an}', $html);
        $this->assertStringNotContainsString('{tinh}', $html);
    }

    public function test_o_so_lieu_thay_dau_bang_so_du_an_that(): void
    {
        $soDuAn = DB::table('products')
            ->whereNull('deleted_at')->where('publish', 2)->count();

        $html = $this->get('/du-an')->assertOk()->getContent();

        $this->assertStringContainsString(
            '>' . number_format($soDuAn, 0, ',', '.') . '<',
            $html,
            'O so lieu khong hien dung tong so du an'
        );
    }

    public function test_o_so_lieu_khong_hien_khi_quan_tri_bo_trong(): void
    {
        $cu = DB::table('introduces')
            ->where('keyword', 'project_stat_3_label')->where('language_id', 1)
            ->value('content');

        DB::table('introduces')
            ->where('keyword', 'project_stat_3_label')->where('language_id', 1)
            ->update(['content' => '']);
        DB::table('introduces')
            ->where('keyword', 'project_stat_3_value')->where('language_id', 1)
            ->update(['content' => '']);

        try {
            $html = $this->get('/du-an')->assertOk()->getContent();
            $this->assertStringNotContainsString('Khách hàng quan tâm', $html);
        } finally {
            DB::table('introduces')
                ->where('keyword', 'project_stat_3_label')->where('language_id', 1)
                ->update(['content' => $cu]);
            DB::table('introduces')
                ->where('keyword', 'project_stat_3_value')->where('language_id', 1)
                ->update(['content' => '15.250+']);
        }
    }

    public function test_cot_phai_co_du_ba_khoi(): void
    {
        $html = $this->get('/du-an')->assertOk()->getContent();

        $this->assertStringContainsString('Bản đồ dự án', $html, 'Thieu khoi ban do');
        $this->assertStringContainsString('Xem thêm tỉnh thành', $html, 'Thieu nut xem them tinh thanh');
        $this->assertStringContainsString('Tin tức nổi bật', $html, 'Thieu khoi tin tuc noi bat');
        $this->assertStringContainsString('Có dự án phù hợp với bạn?', $html, 'Thieu khoi moi tu van');
    }

    public function test_bo_loc_co_chu_xoa_loc_va_nut_ap_dung(): void
    {
        $html = $this->get('/du-an')->assertOk()->getContent();

        $this->assertStringContainsString('Xóa lọc', $html);
        $this->assertStringContainsString('ÁP DỤNG BỘ LỌC', $html);
    }

    public function test_ghim_ban_do_ve_dung_so_tinh_co_du_an(): void
    {
        $soTinh = DB::table('products')
            ->whereNull('deleted_at')->where('publish', 2)
            ->whereNotNull('province_code')
            ->join('vn_provinces as pr', 'pr.code', '=', 'products.province_code')
            ->whereNotNull('pr.lat')
            ->distinct()->count('products.province_code');

        $html = $this->get('/du-an')->assertOk()->getContent();

        // Khoi ban do o cot phai va (neu co) hinh o noi khac dung chung mot
        // component; dem trong PHAN cot phai bang cach dem lop ghim.
        $this->assertSame(
            $soTinh,
            substr_count($html, 'class="nx-vnmap__ghim'),
            'So ghim tren ban do khong bang so tinh co du an'
        );
    }

    public function test_moi_tinh_deu_co_toa_do(): void
    {
        $thieu = DB::table('vn_provinces')
            ->where(function ($q) {
                $q->whereNull('lat')->orWhereNull('lng');
            })
            ->pluck('name')->all();

        $this->assertSame([], $thieu, 'Tinh chua co toa do: ' . implode(', ', $thieu));
    }

    public function test_trang_ban_do_mo_duoc(): void
    {
        $this->get('/du-an/ban-do')->assertOk()->assertSee('Bản đồ dự án', false);
    }

    public function test_trang_ban_do_loc_theo_tinh_thi_hien_du_an_cua_tinh(): void
    {
        $tinh = DB::table('products as p')
            ->join('vn_provinces as pr', 'pr.code', '=', 'p.province_code')
            ->whereNull('p.deleted_at')->where('p.publish', 2)
            ->groupBy('p.province_code', 'pr.name')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->first(['p.province_code', 'pr.name', DB::raw('COUNT(*) as so')]);

        if (!$tinh) {
            $this->markTestSkipped('Chua co du an nao gan tinh.');
        }

        $this->get('/du-an/ban-do?province_code=' . $tinh->province_code)
            ->assertOk()
            ->assertSee($tinh->so . ' dự án tại', false);
    }

    public function test_ma_tinh_khong_co_du_an_thi_ve_ban_do_toan_quoc(): void
    {
        // 96 = Ca Mau; neu tinh nay co du an that thi bo qua.
        $co = DB::table('products')->whereNull('deleted_at')
            ->where('publish', 2)->where('province_code', '96')->exists();

        if ($co) {
            $this->markTestSkipped('Ca Mau dang co du an, khong thu duoc truong hop rong.');
        }

        $this->get('/du-an/ban-do?province_code=96')
            ->assertOk()
            ->assertDontSee('dự án tại Cà Mau', false);
    }

    public function test_the_tinh_o_cot_phai_dan_sang_trang_ban_do(): void
    {
        $ma = DB::table('products')->whereNull('deleted_at')
            ->where('publish', 2)->whereNotNull('province_code')
            ->value('province_code');

        if (!$ma) {
            $this->markTestSkipped('Chua co du an nao gan tinh.');
        }

        $this->get('/du-an')->assertOk()
            ->assertSee(url('/du-an/ban-do?province_code=' . $ma), false);
    }

    public function test_the_du_an_hien_du_ba_o_thong_so(): void
    {
        $html = $this->get('/du-an')->assertOk()->getContent();

        $this->assertStringContainsString('Diện tích', $html);
        $this->assertStringContainsString('Số căn', $html);
        $this->assertStringContainsString('Tiến độ', $html);
    }
}
