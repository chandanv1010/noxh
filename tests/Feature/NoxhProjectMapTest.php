<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Trang Ban do du an - /du-an/ban-do.
 *
 * Ghim duoc giao ra duoi dang JSON trong trang, nen test doc thang khoi JSON
 * do: no la dung thu JS se ve len ban do.
 */
class NoxhProjectMapTest extends TestCase
{
    public function test_trang_ban_do_mo_duoc_va_co_du_ghim(): void
    {
        $html = $this->get('/du-an/ban-do')->assertOk()->getContent();

        $this->assertStringContainsString('data-nx-bando', $html);

        $soCoToaDo = DB::table('products')
            ->where('publish', 2)->whereNull('deleted_at')
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->count();

        $this->assertCount($soCoToaDo, $this->ghim($html), 'Thieu ghim so voi so du an co toa do');
    }

    /** Moi ghim phai du thu de ve the thong tin va dan sang trang chi tiet. */
    public function test_moi_ghim_du_thong_tin_cho_the(): void
    {
        $ghim = $this->ghim($this->get('/du-an/ban-do')->getContent());

        $this->assertNotEmpty($ghim);

        foreach ($ghim as $g) {
            foreach (['id', 'ten', 'url', 'anh', 'lat', 'lng', 'gia'] as $o) {
                $this->assertArrayHasKey($o, $g, "Ghim thieu o {$o}");
            }

            $this->assertNotSame('', trim((string) $g['ten']));
            $this->assertStringContainsString('/du-an/', $g['url']);

            // Toa do 0,0 nam giua Dai Tay Duong - do la dau hieu cot rong bi
            // ep ve so, khong phai mot vi tri that.
            $this->assertNotEquals(0.0, (float) $g['lat']);
            $this->assertNotEquals(0.0, (float) $g['lng']);
        }
    }

    public function test_loc_theo_tinh_va_phuong_xa(): void
    {
        $tinh = DB::table('products')->where('publish', 2)->whereNull('deleted_at')
            ->whereNotNull('province_code')->whereNotNull('latitude')
            ->value('province_code');

        $html = $this->get('/du-an/ban-do?province_code=' . $tinh)->assertOk()->getContent();
        $ghimTinh = $this->ghim($html);

        $this->assertNotEmpty($ghimTinh);
        $this->assertLessThanOrEqual(
            count($this->ghim($this->get('/du-an/ban-do')->getContent())),
            count($ghimTinh)
        );

        // Chon mot tinh thi o phuong/xa phai mo ra (khong con disabled).
        $this->assertStringNotContainsString('id="bd-xa" disabled', $html);

        $xa = DB::table('products')->where('publish', 2)->whereNull('deleted_at')
            ->where('province_code', $tinh)->whereNotNull('ward_code')
            ->whereNotNull('latitude')->value('ward_code');

        if (!$xa) {
            $this->markTestSkipped('Chua du an nao trong tinh nay co phuong/xa.');
        }

        $ghimXa = $this->ghim($this->get('/du-an/ban-do?province_code=' . $tinh . '&ward_code=' . $xa)->getContent());

        $this->assertNotEmpty($ghimXa);
        $this->assertLessThanOrEqual(count($ghimTinh), count($ghimXa));
    }

    /**
     * Ma dia gioi bia dat thi coi nhu khong loc, chu khong tra ve trang trong.
     *
     * Trang trong ma khong giai thich gi thi nguoi dung tuong trang hong.
     */
    public function test_ma_dia_gioi_khong_co_that_thi_bo_qua(): void
    {
        $tatCa = count($this->ghim($this->get('/du-an/ban-do')->getContent()));

        $this->assertCount($tatCa, $this->ghim(
            $this->get('/du-an/ban-do?province_code=999')->assertOk()->getContent()
        ));

        // Phuong/xa khong thuoc tinh dang chon cung bi bo qua, chi con loc tinh.
        $tinh = DB::table('products')->where('publish', 2)->whereNull('deleted_at')
            ->whereNotNull('province_code')->whereNotNull('latitude')->value('province_code');

        $riengTinh = count($this->ghim($this->get('/du-an/ban-do?province_code=' . $tinh)->getContent()));

        $this->assertCount($riengTinh, $this->ghim(
            $this->get('/du-an/ban-do?province_code=' . $tinh . '&ward_code=00000')->assertOk()->getContent()
        ));
    }

    public function test_loc_theo_trang_thai_va_tu_khoa(): void
    {
        $d = DB::table('products as p')
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'p.id')->where('pl.language_id', '=', 1);
            })
            ->where('p.publish', 2)->whereNull('p.deleted_at')
            ->whereNotNull('p.latitude')
            ->first(['p.status', 'pl.name']);

        $this->assertNotNull($d, 'Chua co du an nao co toa do de thu');

        foreach ($this->ghim($this->get('/du-an/ban-do?status[]=' . $d->status)->getContent()) as $g) {
            $this->assertSame($d->status, $g['trangThai']);
        }

        $tu = explode(' ', trim($d->name))[1] ?? $d->name;
        $ghim = $this->ghim($this->get('/du-an/ban-do?tu-khoa=' . urlencode($tu))->getContent());

        $this->assertNotEmpty($ghim, 'Tim theo mot chu trong ten du an ma khong ra gi');
    }

    /**
     * Nen ban do mac dinh la OpenStreetMap. Chon Google ma CHUA dan API key
     * thi phai tu quay ve OpenStreetMap - tai thu vien Google khong co key
     * chi ra mot o xam bao loi.
     */
    public function test_chon_google_ma_thieu_key_thi_quay_ve_openstreetmap(): void
    {
        $cu = DB::table('systems')->where('keyword', 'map_provider')->where('language_id', 1)->value('content');

        try {
            $this->datCaiDat('map_provider', 'google');
            $this->datCaiDat('map_google_key', '');

            $html = $this->get('/du-an/ban-do')->assertOk()->getContent();

            $this->assertStringContainsString('data-nen="osm"', $html);
            $this->assertStringNotContainsString('data-nen="google"', $html);
        } finally {
            $this->datCaiDat('map_provider', $cu ?? 'osm');
            DB::table('systems')->where('keyword', 'map_google_key')->where('language_id', 1)->delete();
        }
    }

    /** Toan bo chu cua trang lay tu bang introduces, khong ghi trong Blade. */
    public function test_chu_tren_trang_lay_tu_quan_tri(): void
    {
        $html = $this->get('/du-an/ban-do')->getContent();

        foreach (['projectmap_heading', 'projectmap_filter_button', 'projectmap_note'] as $khoa) {
            $chu = DB::table('introduces')->where('keyword', $khoa)->where('language_id', 1)->value('content');

            $this->assertNotEmpty($chu, "Thieu o noi dung {$khoa}");
            $this->assertStringContainsString(e($chu), $html, "Khong thay noi dung cua {$khoa} tren trang");
        }
    }

    /**
     * Loc den phuong/xa thi ban do phai phong sat hon loc den tinh.
     *
     * Day la cai nguoi dung doi hoi: vao den tung vi tri du an, chu khong
     * phai mo ca tinh ra roi mot ghim ghi "3 du an".
     */
    public function test_loc_den_phuong_xa_thi_phong_sat_hon(): void
    {
        $d = DB::table('products')->where('publish', 2)->whereNull('deleted_at')
            ->whereNotNull('ward_code')->whereNotNull('latitude')
            ->first(['province_code', 'ward_code']);

        if (!$d) {
            $this->markTestSkipped('Chua du an nao co phuong/xa.');
        }

        if (DB::table('vn_wards')->where('code', $d->ward_code)->value('lat') === null) {
            $this->markTestSkipped('Phuong/xa nay chua co toa do - chay `php artisan noxh:toa-do --xa`.');
        }

        $tinh = $this->zoom($this->get('/du-an/ban-do?province_code=' . $d->province_code)->getContent());
        $xa = $this->zoom($this->get('/du-an/ban-do?province_code=' . $d->province_code . '&ward_code=' . $d->ward_code)->getContent());

        $this->assertGreaterThan($tinh, $xa, 'Loc den phuong/xa ma ban do khong phong sat hon');
    }

    /**
     * Du an phai co toa do RIENG, khong duoc nam dung tam tinh/thanh.
     *
     * Toa do trung khit tam tinh la gia tri du phong: ca may du an trong mot
     * tinh se chong len nhau thanh mot ghim duy nhat. Chay
     * `php artisan noxh:toa-do` de nap toa do that.
     */
    public function test_du_an_khong_con_nam_o_tam_tinh(): void
    {
        $xau = DB::table('products as p')
            ->join('product_language as pl', function ($j) {
                $j->on('pl.product_id', '=', 'p.id')->where('pl.language_id', '=', 1);
            })
            ->join('vn_provinces as pr', 'pr.code', '=', 'p.province_code')
            ->where('p.publish', 2)->whereNull('p.deleted_at')
            ->whereNotNull('p.latitude')->whereNotNull('pr.lat')
            ->whereRaw('ROUND(p.latitude, 4) = ROUND(pr.lat, 4)')
            ->whereRaw('ROUND(p.longitude, 4) = ROUND(pr.lng, 4)')
            ->pluck('pl.name');

        $this->assertEmpty(
            $xau,
            'Cac du an sau van dung toa do tam tinh/thanh, chay `php artisan noxh:toa-do`: ' . $xau->implode(', ')
        );
    }

    // -------------------------------------------------------------------------

    /** Muc phong ban do ghi trong thuoc tinh data-zoom cua khung ban do. */
    private function zoom(string $html): int
    {
        preg_match('/data-zoom="(\d+)"/', $html, $m);

        return (int) ($m[1] ?? 0);
    }

    /** Doc khoi JSON ghim nhung trong trang. */
    private function ghim(string $html): array
    {
        if (!preg_match('#<script type="application/json" data-nx-bando-diem>(.*?)</script>#s', $html, $m)) {
            $this->fail('Khong thay khoi du lieu ghim trong trang ban do');
        }

        return json_decode(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'), true) ?: [];
    }

    private function datCaiDat(string $khoa, string $giaTri): void
    {
        DB::table('systems')->updateOrInsert(
            ['keyword' => $khoa, 'language_id' => 1],
            ['content' => $giaTri, 'user_id' => 1, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
