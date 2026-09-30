<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Trang danh muc tin tuc va trang chi tiet tin
 * (ban ve noxh_image/tin-tuc-fix.webp).
 *
 * Trong tam: hai trang dung chung hai cot phu, danh sach la MOT DONG MOT TIN
 * chu khong phai luoi the, va khong con chuoi nao viet cung trong Blade.
 */
class NoxhNewsPageTest extends TestCase
{
    public function test_trang_danh_muc_ve_du_ba_cot(): void
    {
        $html = $this->get('/tin-tuc')->assertOk()->getContent();

        foreach (['nx-tin-dai', 'nx-tin-muc', 'nx-tin-ds', 'nx-tin-dong', 'nx-tin-lq'] as $khoi) {
            $this->assertStringContainsString($khoi, $html, "Thieu khoi {$khoi}");
        }

        // Danh sach phai la dong ngang, khong phai luoi the nhu ban cu.
        $this->assertStringNotContainsString('nx-article-grid', $html);
    }

    public function test_loc_theo_chuyen_muc_va_bao_404_khi_khong_co(): void
    {
        $muc = DB::table('post_catalogue_language')
            ->where('language_id', 1)->where('canonical', 'chinh-sach')->first();

        $this->assertNotNull($muc, 'Chua co chuyen muc Chinh sach de kiem tra');

        $html = $this->get('/tin-tuc/chuyen-muc/chinh-sach')->assertOk()->getContent();
        $this->assertStringContainsString($muc->name, $html);

        $this->get('/tin-tuc/chuyen-muc/khong-he-co-muc-nay')->assertNotFound();
    }

    /**
     * Bai viet phai doc ra DU noi dung, chu thich anh va tu khoa.
     *
     * Truoc day truy van goi first([...cot...]) sau khi da select() nen
     * Laravel bo qua danh sach cot do: trang chi tiet ra mot bai trong ruot,
     * khong co than bai lan hang tu khoa.
     */
    public function test_trang_chi_tiet_in_du_noi_dung_chu_thich_va_tu_khoa(): void
    {
        $bai = $this->baiCoNoiDung();

        $html = $this->get('/tin-tuc/' . $bai->canonical)->assertOk()->getContent();

        $this->assertStringContainsString('nx-tin-bai__noi', $html);
        $this->assertStringContainsString('nx-tin-khoa', $html);
        $this->assertStringContainsString('<figcaption>', $html);

        // Mot doan chu that trong than bai phai co mat.
        $mot = trim(strip_tags(mb_substr((string) $bai->content, 0, 400)));
        $mot = mb_substr(trim(explode("\n", $mot)[0]), 0, 40);
        $this->assertStringContainsString(e($mot), $html);
    }

    public function test_mo_bai_viet_thi_tang_luot_xem(): void
    {
        $bai = $this->baiCoNoiDung();
        $truoc = (int) DB::table('posts')->where('id', $bai->post_id)->value('viewed');

        $this->get('/tin-tuc/' . $bai->canonical)->assertOk();

        $sau = (int) DB::table('posts')->where('id', $bai->post_id)->value('viewed');
        $this->assertSame($truoc + 1, $sau);
    }

    /**
     * Doi mot o chu trong quan tri thi ca hai trang doi theo - cach duy nhat
     * chung minh khong con chuoi nao viet cung trong Blade.
     */
    public function test_chu_tren_trang_lay_tu_quan_tri(): void
    {
        $o = [
            'news_heading' => 'TIEU DE THU NGHIEM',
            'news_cat_heading' => 'Danh muc thu nghiem',
            'news_help_button' => 'Nut thu nghiem',
            'news_related_heading' => 'Lien quan thu nghiem',
        ];

        $cu = DB::table('introduces')->whereIn('keyword', array_keys($o))
            ->where('language_id', 1)->pluck('content', 'keyword')->all();

        foreach ($o as $khoa => $gia) {
            DB::table('introduces')->where('keyword', $khoa)->where('language_id', 1)
                ->update(['content' => $gia]);
        }

        try {
            $html = $this->get('/tin-tuc')->assertOk()->getContent();

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

    /** Dai dau trang dung dung o anh chung cua cac trang trong. */
    public function test_dai_dau_trang_dung_anh_trong_cau_hinh(): void
    {
        $anh = '/uploads/noxh/kiem-tra-dai-dau-trang.png';

        $cu = DB::table('introduces')->where('keyword', 'pagehead_image')
            ->where('language_id', 1)->value('content');

        DB::table('introduces')->where('keyword', 'pagehead_image')
            ->where('language_id', 1)->update(['content' => $anh]);

        try {
            $html = $this->get('/tin-tuc')->assertOk()->getContent();

            $this->assertStringContainsString($anh, $html);
            $this->assertStringContainsString('nx-tin-dai co-nen', $html);
        } finally {
            DB::table('introduces')->where('keyword', 'pagehead_image')
                ->where('language_id', 1)->update(['content' => $cu]);
        }
    }

    private function baiCoNoiDung()
    {
        $bai = DB::table('post_language as pl')
            ->join('posts as p', 'p.id', '=', 'pl.post_id')
            ->where('pl.language_id', 1)
            ->where('p.publish', 2)
            ->whereNull('p.deleted_at')
            ->whereRaw("CHAR_LENGTH(COALESCE(pl.content, '')) > 400")
            ->whereRaw("COALESCE(pl.meta_keyword, '') <> ''")
            ->whereRaw("COALESCE(p.image_caption, '') <> ''")
            ->first(['pl.post_id', 'pl.canonical', 'pl.content']);

        $this->assertNotNull($bai, 'Chua co bai viet mau du du lieu de kiem tra');

        return $bai;
    }
}
