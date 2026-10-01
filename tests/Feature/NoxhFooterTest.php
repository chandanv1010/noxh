<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Chan trang, dung theo ban ve noxh_image/tin-tuc-fix.webp.
 *
 * Cac cot lien ket, khoi Lien he, dia mang xa hoi, nut len dau trang va dai
 * duoi cung co khau hieu.
 */
class NoxhFooterTest extends TestCase
{
    public function test_cac_cot_lien_ket_lay_tu_nhom_menu_chan(): void
    {
        $html = $this->get('/tin-tuc')->assertOk()->getContent();

        $cot = DB::table('menus as m')
            ->join('menu_catalogues as mc', 'mc.id', '=', 'm.menu_catalogue_id')
            ->join('menu_language as ml', function ($j) {
                $j->on('ml.menu_id', '=', 'm.id')->where('ml.language_id', '=', 1);
            })
            ->where('mc.keyword', 'footer-menu')
            ->where('m.parent_id', 0)
            ->where('m.publish', 2)
            ->pluck('ml.name');

        $this->assertGreaterThanOrEqual(2, $cot->count(), 'Chan trang phai co it nhat hai cot lien ket');

        foreach ($cot as $ten) {
            $this->assertStringContainsString(e($ten), $html, "Thieu cot \"{$ten}\" o chan trang");
        }
    }

    /**
     * Khoi "Lien he" doc thang Cau hinh he thong, khong qua menu - so dien
     * thoai va dia chi la thong tin he thong, de hai noi thi se lech nhau.
     */
    public function test_khoi_lien_he_lay_tu_cau_hinh_he_thong(): void
    {
        $html = $this->get('/tin-tuc')->assertOk()->getContent();

        $email = trim((string) $this->caiDat('contact_email'));
        $diaChi = trim((string) $this->caiDat('contact_address'));
        $hotline = nx_hotline_dau((string) $this->caiDat('contact_hotline'));

        if ($email === '' && $diaChi === '' && !$hotline) {
            $this->markTestSkipped('Chua khai thong tin lien he trong Cau hinh he thong.');
        }

        foreach (array_filter([$hotline, $email, $diaChi]) as $gia) {
            $this->assertStringContainsString(e($gia), $html, "Chan trang thieu \"{$gia}\"");
        }
    }

    public function test_co_nut_len_dau_trang_va_khau_hieu(): void
    {
        $html = $this->get('/tin-tuc')->assertOk()->getContent();

        $this->assertStringContainsString('nx-footer__len', $html, 'Thieu nut len dau trang');

        $khau = trim((string) DB::table('introduces')->where('keyword', 'footer_slogan')
            ->where('language_id', 1)->value('content'));

        $this->assertNotEmpty($khau);
        $this->assertStringContainsString('nx-footer__cau', $html);
        $this->assertStringContainsString(e($khau), $html);
    }

    /** Dia mang xa hoi nao co duong dan thi hien, khong co thi thoi. */
    public function test_chi_hien_mang_xa_hoi_da_khai(): void
    {
        $html = $this->get('/tin-tuc')->assertOk()->getContent();

        foreach (['facebook', 'youtube', 'tiktok'] as $ma) {
            $url = trim((string) $this->caiDat('social_' . $ma));
            $co = str_contains($html, 'nx-footer__social--' . $ma);

            $this->assertSame($url !== '', $co, "Dia {$ma} hien khong dung voi cai dat");
        }
    }

    private function caiDat(string $khoa)
    {
        return DB::table('systems')->where('keyword', $khoa)->where('language_id', 1)->value('content');
    }
}
