<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\User;
use App\Services\V1\Legal\LegalDocumentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tai file van ban len tu form quan tri.
 *
 * File duoc luu thang vao public/uploads/van-ban chu khong qua kho anh:
 * kho anh cua ban clone chi nhan anh, ma van ban phap luat thi chu yeu la PDF
 * va Word.
 */
class NoxhLegalUploadTest extends TestCase
{
    private ?User $quanTri = null;
    private array $donDep = [];
    private ?int $vanBanId = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quanTri = User::whereHas('user_catalogues.permissions', function ($q) {
            $q->where('canonical', 'legal.document.create');
        })->where('publish', 2)->first();
    }

    protected function tearDown(): void
    {
        foreach ($this->donDep as $duong) {
            if (is_file($duong)) {
                unlink($duong);
            }
        }

        if ($this->vanBanId) {
            DB::table('legal_documents')->where('id', $this->vanBanId)->delete();
        }

        parent::tearDown();
    }

    private function boQuanTri(): void
    {
        if (!$this->quanTri) {
            $this->markTestSkipped('Không có tài khoản quản trị nào có quyền legal.document.create.');
        }
    }

    private function duLieu(array $them = []): array
    {
        return array_merge([
            'title' => 'Văn bản thử nghiệm tự động',
            'doc_type' => 'nghi-dinh',
            'doc_number' => '99/2026/NĐ-CP',
            'issued_date' => '2026-01-15',
            'effective_date' => '2026-03-01',
            'summary' => 'Nội dung tóm tắt thử nghiệm.',
            'publish' => 2,
            'order' => 0,
        ], $them);
    }

    private function batVanBan(): ?LegalDocument
    {
        $vb = LegalDocument::where('title', 'Văn bản thử nghiệm tự động')->latest('id')->first();

        if ($vb) {
            $this->vanBanId = $vb->id;

            if ($vb->file && str_starts_with($vb->file, '/' . LegalDocumentService::THU_MUC)) {
                $this->donDep[] = public_path(ltrim($vb->file, '/'));
            }
        }

        return $vb;
    }

    public function test_tai_len_file_pdf(): void
    {
        $this->boQuanTri();

        $this->actingAs($this->quanTri)->post(route('legal.document.store'), $this->duLieu([
            'tep_tai_len' => UploadedFile::fake()->create('Nghi dinh 99.pdf', 120, 'application/pdf'),
        ]))->assertRedirect();

        $vb = $this->batVanBan();

        $this->assertNotNull($vb, 'Không lưu được văn bản');
        $this->assertSame('pdf', $vb->file_type);
        $this->assertGreaterThan(0, (int) $vb->file_size, 'Không ghi lại dung lượng file');
        $this->assertStringStartsWith('/' . LegalDocumentService::THU_MUC . '/', $vb->file);
        $this->assertFileExists(public_path(ltrim($vb->file, '/')), 'File không nằm trên đĩa');

        // Ten file phai duoc chuan hoa: khong con dau cach, con nhan ra duoc.
        $this->assertStringContainsString('nghi-dinh-99', basename($vb->file));
    }

    public function test_tai_len_file_word(): void
    {
        $this->boQuanTri();

        $this->actingAs($this->quanTri)->post(route('legal.document.store'), $this->duLieu([
            'tep_tai_len' => UploadedFile::fake()->create('mau-don.docx', 40),
        ]))->assertRedirect();

        $vb = $this->batVanBan();

        $this->assertNotNull($vb);
        $this->assertSame('docx', $vb->file_type);
    }

    public function test_tu_choi_dinh_dang_khong_cho_phep(): void
    {
        $this->boQuanTri();

        $this->actingAs($this->quanTri)->post(route('legal.document.store'), $this->duLieu([
            'tep_tai_len' => UploadedFile::fake()->create('ma-doc.exe', 10),
        ]))->assertSessionHasErrors('tep_tai_len');

        $this->assertNull(
            LegalDocument::where('title', 'Văn bản thử nghiệm tự động')->first(),
            'File .exe vẫn được lưu'
        );
    }

    public function test_sua_van_ban_ma_khong_chon_file_thi_giu_file_cu(): void
    {
        $this->boQuanTri();

        // Diem de hong: form sua khong gui lai file da co, neu ma nguon ghi de
        // bang chuoi rong thi van ban mat file ngay lan sua dau tien.
        $this->actingAs($this->quanTri)->post(route('legal.document.store'), $this->duLieu([
            'tep_tai_len' => UploadedFile::fake()->create('van-ban-goc.pdf', 30, 'application/pdf'),
        ]));

        $vb = $this->batVanBan();
        $this->assertNotNull($vb);
        $fileCu = $vb->file;

        $this->actingAs($this->quanTri)->post(
            route('legal.document.update', $vb->id),
            $this->duLieu(['title' => 'Văn bản thử nghiệm tự động', 'file' => $fileCu])
        );

        $vb->refresh();

        $this->assertSame($fileCu, $vb->file, 'Sửa văn bản làm mất file đã tải lên');
    }

    public function test_dung_luong_hien_ra_cho_de_doc(): void
    {
        $this->assertSame('1,5 MB', dung_luong(1572864));
        $this->assertSame('500 KB', dung_luong(512000));
        $this->assertSame('120 B', dung_luong(120));
        $this->assertSame('', dung_luong(0));
    }
}
