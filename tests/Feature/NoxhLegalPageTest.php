<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Trang Phong phap ly NOXH (ban ve noxh_image/plxh fix.jpg).
 *
 * Trong tam la: khong con chuoi nao viet cung trong Blade - sua o quan tri
 * la trang ngoai doi theo - va the giay to mang mau dung theo duoi file.
 */
class NoxhLegalPageTest extends TestCase
{
    public function test_trang_mo_duoc_va_ve_du_cac_khoi(): void
    {
        $html = $this->get('/phap-ly-noxh')->assertOk()->getContent();

        foreach (['nx-pl__gioi', 'nx-chip', 'nx-pl-topics', 'nx-pl-posts', 'nx-vb', 'nx-pl-trust'] as $khoi) {
            $this->assertStringContainsString($khoi, $html, "Thieu khoi {$khoi}");
        }
    }

    /**
     * Doi mot o chu trong quan tri thi trang ngoai phai doi theo - day la
     * cach duy nhat chung minh trang khong viet cung chuoi nao.
     */
    public function test_chu_tren_trang_lay_tu_quan_tri(): void
    {
        $o = [
            'legal_heading' => 'TIEU DE THU NGHIEM',
            'legal_topic_1_title' => 'Chu de thu nghiem',
            'legal_trust_1_title' => 'Cam ket thu nghiem',
            'legal_cta_button' => 'Nut thu nghiem',
        ];

        $cu = DB::table('introduces')->whereIn('keyword', array_keys($o))
            ->where('language_id', 1)->pluck('content', 'keyword')->all();

        foreach ($o as $khoa => $gia) {
            DB::table('introduces')->where('keyword', $khoa)->where('language_id', 1)
                ->update(['content' => $gia]);
        }

        try {
            $html = $this->get('/phap-ly-noxh')->assertOk()->getContent();

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

    /**
     * The giay to mang mau theo duoi file: PDF mot mau, Word mot mau. Truoc
     * day dung chung mot the nen nhin khong biet bam vao se tai ve cai gi.
     */
    public function test_the_van_ban_doi_mau_theo_duoi_file(): void
    {
        $id = DB::table('legal_documents')->insertGetId([
            'title' => 'Van ban thu nghiem dang Word',
            'doc_number' => 'TN-' . time(),
            'doc_type' => 'other',
            'file' => '/uploads/noxh/van-ban-mau.docx',
            'file_type' => 'docx',
            'is_featured' => 1,
            'publish' => 2,
            'order' => 0,
            'effective_date' => now()->addYear()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $html = $this->get('/phap-ly-noxh')->assertOk()->getContent();

            $this->assertStringContainsString('Van ban thu nghiem dang Word', $html);
            $this->assertStringContainsString('nx-vb__icon--doc', $html);

            // Duoi ".docx" phai in gon lai thanh "DOC" cho vua the.
            $this->assertMatchesRegularExpression('/nx-vb__icon--doc[\s\S]{0,4000}<i>DOC<\/i>/', $html);
        } finally {
            DB::table('legal_documents')->where('id', $id)->delete();
        }
    }

    /** Van ban chua co tom tat thi dong ngay phai in kem chu "Co hieu luc tu". */
    public function test_van_ban_khong_co_tom_tat_thi_in_ngay_hieu_luc(): void
    {
        $id = DB::table('legal_documents')->insertGetId([
            'title' => 'Van ban thu nghiem khong tom tat',
            'doc_number' => 'TN2-' . time(),
            'doc_type' => 'other',
            'summary' => null,
            'is_featured' => 1,
            'publish' => 2,
            'order' => 0,
            'effective_date' => '2030-01-15',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            $html = $this->get('/phap-ly-noxh')->assertOk()->getContent();

            $this->assertStringContainsString('Có hiệu lực từ 15/01/2030', $html);
        } finally {
            DB::table('legal_documents')->where('id', $id)->delete();
        }
    }
}
