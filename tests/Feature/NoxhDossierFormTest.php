<?php

namespace Tests\Feature;

use App\Models\DossierItem;
use App\Models\DossierSet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Form "Điền form để nhận hồ sơ" ở cuối trang /ho-so.
 *
 * Ba dieu phai giu, va ca ba deu la ly do ton tai cua bai kiem tra nay:
 *
 *  1. Form nam o CUOI trang. Truoc day cho nay la the chuyen gia dat o cot phu;
 *     tren dien thoai cot phu bi day len TRUOC noi dung nen the do hien ra lung
 *     lo giua trang, cat ngang danh sach ho so dang doc.
 *  2. O "Doi tuong" do DONG tu CSDL. Viet cung danh sach nhom la moi lan quan tri
 *     them nhom doi tuong lai phai sua ma nguon.
 *  3. Gui form phai luu duoc thong tin va gui Telegram, giong moi form khac.
 */
class NoxhDossierFormTest extends TestCase
{
    private array $idNhomTam = [];
    private array $caiDatCu = [];

    protected function tearDown(): void
    {
        if ($this->idNhomTam) {
            DB::table('dossier_items')->whereIn('dossier_set_id', $this->idNhomTam)->delete();
            DB::table('dossier_sets')->whereIn('id', $this->idNhomTam)->delete();
            $this->idNhomTam = [];
        }

        DB::table('contacts')->where('phone', '0911777999')->delete();

        foreach ($this->caiDatCu as $keyword => $cu) {
            if ($cu === null) {
                DB::table('systems')->where('keyword', $keyword)->delete();
            } else {
                DB::table('systems')->where('keyword', $keyword)->update(['content' => $cu]);
            }
        }

        parent::tearDown();
    }

    /** Hai nhom doi tuong tam, moi nhom mot giay to — de bai kiem tra khong phu
     *  thuoc du lieu that cua may dang chay. */
    private function dungHaiNhom(): void
    {
        foreach ([['Nhóm thử nghiệm A', 'nhom-thu-a'], ['Nhóm thử nghiệm B', 'nhom-thu-b']] as $i => [$ten, $canonical]) {
            $bo = DossierSet::create([
                'name' => $ten,
                'canonical' => $canonical,
                'description' => 'Nhóm tạm cho bài kiểm tra tự động',
                'publish' => 2,
                'order' => 900 + $i,
            ]);

            $this->idNhomTam[] = $bo->id;

            DossierItem::create([
                'dossier_set_id' => $bo->id,
                'title' => 'Giấy tờ thử nghiệm ' . ($i + 1),
                'description' => 'Mô tả giấy tờ thử nghiệm',
                'issued_by' => 'UBND phường/xã',
                'copies' => 2,
                'is_required' => 1,
                'publish' => 2,
                'order' => 0,
            ]);
        }
    }

    private function datCaiDat(string $keyword, string $giaTri): void
    {
        if (!array_key_exists($keyword, $this->caiDatCu)) {
            $this->caiDatCu[$keyword] = DB::table('systems')->where('keyword', $keyword)->value('content');
        }

        if (DB::table('systems')->where('keyword', $keyword)->exists()) {
            DB::table('systems')->where('keyword', $keyword)->update(['content' => $giaTri]);
        } else {
            DB::table('systems')->insert([
                'keyword' => $keyword,
                'content' => $giaTri,
                'language_id' => DB::table('languages')->where('canonical', 'vn')->value('id'),
                'user_id' => DB::table('users')->min('id'),
            ]);
        }
    }

    public function test_trang_ho_so_co_form_o_cuoi_trang(): void
    {
        $this->dungHaiNhom();

        $html = $this->get('/ho-so')->assertOk()->getContent();

        $this->assertStringContainsString('nx-nhan-ho-so', $html, 'Không thấy form nhận hồ sơ');
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="phone"', $html);
        $this->assertStringContainsString('name="interest"', $html);
        $this->assertStringContainsString('value="ho-so"', $html);

        // Form phai nam SAU danh sach giay to, khong phai chen giua.
        $vtForm = strpos($html, 'nx-nhan-ho-so');
        $vtGiayTo = strrpos($html, 'nx-dossier-item');

        if ($vtGiayTo !== false) {
            $this->assertGreaterThan($vtGiayTo, $vtForm, 'Form không nằm ở cuối trang');
        }
    }

    public function test_the_chuyen_gia_khong_con_o_trang_ho_so(): void
    {
        $html = $this->get('/ho-so')->assertOk()->getContent();

        $this->assertStringNotContainsString('nx-expert', $html, 'Thẻ chuyên gia vẫn còn trên trang Hồ sơ');
    }

    public function test_o_doi_tuong_do_dong_tu_csdl(): void
    {
        $this->dungHaiNhom();

        $html = $this->get('/ho-so')->assertOk()->getContent();

        $this->assertStringContainsString('Nhóm thử nghiệm A', $html);
        $this->assertStringContainsString('Nhóm thử nghiệm B', $html);

        // Va phai la mot o chon keo xuong, khong phai o nhap chu.
        $this->assertMatchesRegularExpression('/<select[^>]*name="interest"/', $html);
    }

    public function test_chon_san_nhom_dang_loc(): void
    {
        $this->dungHaiNhom();

        $html = $this->get('/ho-so?bo=nhom-thu-b')->assertOk()->getContent();

        // Nhom dang xem phai duoc chon san, khoi bat nguoi dung chon lai lan hai.
        $this->assertMatchesRegularExpression(
            '/<option value="Nhóm thử nghiệm B"\s+selected/',
            $html,
            'Không chọn sẵn nhóm đang lọc'
        );
    }

    public function test_gui_form_luu_thong_tin_kem_nhom_doi_tuong(): void
    {
        Http::fake();

        // Phai co cau hinh Telegram thi TelegramService moi goi HTTP. Thieu buoc
        // nay thi phep kiem tra "co gui Telegram khong" luon that bai - khong
        // phai vi tinh nang hong, ma vi chua bat.
        $this->datCaiDat('telegram_bot_token', '123456789:' . str_repeat('A', 35));
        $this->datCaiDat('telegram_chat_id', '123456');

        $this->dungHaiNhom();

        $this->post('/de-lai-thong-tin', [
            'name' => 'Khách thử nghiệm hồ sơ',
            'phone' => '0911777999',
            'interest' => 'Nhóm thử nghiệm A',
            'source' => 'ho-so',
        ])->assertRedirect();

        $dong = DB::table('contacts')->where('phone', '0911777999')->latest('id')->first();

        $this->assertNotNull($dong, 'Không lưu được thông tin khách');
        $this->assertSame('ho-so', $dong->source);
        $this->assertSame('Nhóm thử nghiệm A', $dong->interest, 'Không lưu nhóm đối tượng khách chọn');
        $this->assertSame('new', $dong->status);

        Http::assertSent(function ($yeuCau) {
            $chu = $yeuCau['text'] ?? '';

            return str_contains($yeuCau->url(), 'api.telegram.org')
                && str_contains($chu, 'Nhóm thử nghiệm A')
                && str_contains($chu, 'Trang Hồ sơ')
                // Form nay dung o `interest` de chua NHOM DOI TUONG, nen nhan phai
                // la "Đối tượng". De nguyen "Quan tâm" thi chuyen vien doc khong
                // hieu khach dang noi ve cai gi.
                && str_contains($chu, 'Đối tượng:')
                && !str_contains($chu, 'Quan tâm:');
        });
    }

    public function test_gui_duoc_khi_khach_khong_chon_doi_tuong(): void
    {
        Http::fake();

        // O chon co `required` o phia trinh duyet, nhung may chu KHONG duoc tu choi
        // mot thong tin lien he chi vi thieu mot o khong bat buoc - mat khach oan.
        $this->post('/de-lai-thong-tin', [
            'name' => 'Khách không chọn nhóm',
            'phone' => '0911777999',
            'source' => 'ho-so',
        ])->assertRedirect();

        $this->assertNotNull(
            DB::table('contacts')->where('phone', '0911777999')->latest('id')->first(),
            'Thiếu ô Đối tượng mà không lưu được thông tin khách'
        );
    }

    public function test_loi_xac_nhan_noi_dung_viec_khach_vua_lam(): void
    {
        Http::fake();

        $tra = $this->post('/de-lai-thong-tin', [
            'name' => 'Khách thử nghiệm hồ sơ',
            'phone' => '0911777999',
            'interest' => 'Nhóm thử nghiệm A',
            'source' => 'ho-so',
        ]);

        $tra->assertSessionHas('nx_success', function ($chu) {
            return str_contains($chu, 'mẫu đơn');
        });
    }

    /**
     * Thong bao sau khi gui phai hien o DAU trang.
     *
     * Form nam o cuoi trang, ma gui xong thi may chu tra ve dau trang. Dat thong
     * bao canh form thi nguoi dung khong bao gio nhin thay no - ho chi thay trang
     * tu nhien nhay ve dau va tuong form bi loi.
     */
    public function test_thong_bao_hien_o_dau_trang_chu_khong_canh_form(): void
    {
        $this->dungHaiNhom();

        $html = $this->withSession(['nx_success' => 'Đã gửi thông tin cho chuyên viên phụ trách.'])
            ->get('/ho-so')
            ->assertOk()
            ->getContent();

        $vtThongBao = strpos($html, 'Đã gửi thông tin cho chuyên viên phụ trách.');
        $vtGiayToDauTien = strpos($html, 'nx-dossier-item');

        $this->assertNotFalse($vtThongBao, 'Không thấy thông báo trong trang');
        $this->assertLessThan(
            $vtGiayToDauTien,
            $vtThongBao,
            'Thông báo nằm sau danh sách hồ sơ - người dùng sẽ không nhìn thấy'
        );
    }

    /**
     * Mo ta luu dang HTML entity van phai hien ra chu co dau.
     *
     * Trinh soan thao luu "sách" thanh "s&aacute;ch". Ham strip_tags khong dung
     * toi entity, ma Blade `{{ }}` lai escape dau `&` - nen nguoi doc nhan duoc
     * nguyen si "s&aacute;ch" tren trang. Loi nay co that tren may chu.
     */
    public function test_mo_ta_luu_dang_html_entity_van_hien_dung_chu_co_dau(): void
    {
        $this->dungHaiNhom();

        DossierSet::where('id', $this->idNhomTam[0])->update([
            'description' => 'Danh s&aacute;ch gi&#7845;y t&#7901; c&#7847;n chu&#7849;n b&#7883;.',
        ]);

        $html = $this->get('/ho-so')->assertOk()->getContent();

        $this->assertStringContainsString('Danh sách giấy tờ cần chuẩn bị.', $html);
        $this->assertStringNotContainsString('s&aacute;ch', $html, 'Còn thấy entity thô trong trang');
        $this->assertStringNotContainsString('s&amp;aacute;ch', $html, 'Entity bị escape hai lần');
    }

    public function test_trang_ho_so_co_nut_download_tron_bo(): void
    {
        $this->dungHaiNhom();

        $html = $this->get('/ho-so')->assertOk()->getContent();

        $this->assertStringContainsString('Download trọn bộ', $html);
        $this->assertStringContainsString('/ho-so/nhom-thu-a/tron-bo', $html);
    }

    public function test_tai_tron_bo_tra_ve_zip_kem_ban_huong_dan(): void
    {
        $this->dungHaiNhom();

        $tra = $this->get('/ho-so/nhom-thu-a/tron-bo')->assertOk();

        $this->assertSame('application/zip', $tra->headers->get('content-type'));
        $this->assertStringContainsString('ho-so-nhom-thu-a.zip', (string) $tra->headers->get('content-disposition'));

        $tep = $tra->baseResponse->getFile()->getPathname();
        $this->assertFileExists($tep, 'Không tìm thấy tệp nén vừa tạo');

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($tep) === true, 'Tệp tải về không phải ZIP hợp lệ');

        $huongDan = $zip->getFromName('Huong-dan-ho-so.html');
        $zip->close();

        $this->assertNotFalse($huongDan, 'Gói ZIP thiếu bản hướng dẫn');
        $this->assertStringContainsString('Nhóm thử nghiệm A', $huongDan);
        $this->assertStringContainsString('Giấy tờ thử nghiệm 1', $huongDan);
        $this->assertStringContainsString('bắt buộc', $huongDan);

        @unlink($tep);
    }

    public function test_tai_tron_bo_dem_luot_tai_cho_tung_giay_to(): void
    {
        $this->dungHaiNhom();

        $truoc = DB::table('dossier_items')->where('dossier_set_id', $this->idNhomTam[0])->value('download_count');

        $tra = $this->get('/ho-so/nhom-thu-a/tron-bo')->assertOk();
        $tep = $tra->baseResponse->getFile()->getPathname();

        $sau = DB::table('dossier_items')->where('dossier_set_id', $this->idNhomTam[0])->value('download_count');

        $this->assertSame((int) $truoc + 1, (int) $sau, 'Không đếm lượt tải');

        @unlink($tep);
    }

    public function test_tai_tron_bo_khong_ton_tai_thi_bao_404(): void
    {
        $this->get('/ho-so/khong-co-nhom-nay/tron-bo')->assertNotFound();
    }

    // --- Popup cua nut "Download tron bo" ------------------------------------

    public function test_nut_download_tron_bo_mo_popup(): void
    {
        $this->dungHaiNhom();

        $html = $this->get('/ho-so/can-chuan-bi')->assertOk()->getContent();

        // Nut mang theo TEN bo ho so de popup noi ro khach dang tai bo nao.
        $this->assertStringContainsString('data-nx-tron-bo="Nhóm thử nghiệm A"', $html);
        $this->assertStringContainsString('data-nx-ho-so-modal', $html, 'Không thấy popup trên trang');
    }

    /**
     * Popup chi duoc in MOT lan du trang co bao nhieu bo ho so. In lai moi bo
     * mot popup thi trang co nhieu the cung id, va JS se gan vao nham cai dau.
     */
    public function test_popup_chi_in_mot_lan_cho_ca_trang(): void
    {
        $this->dungHaiNhom();

        $html = $this->get('/ho-so/can-chuan-bi')->assertOk()->getContent();

        // Dem theo id, khong dem theo data-nx-ho-so-modal: ten thuoc tinh do
        // con xuat hien lan nua trong doan JS querySelector o cuoi trang.
        $this->assertSame(1, substr_count($html, 'id="nx-hs-ten"'));
        $this->assertSame(1, substr_count($html, 'id="nx-hs-doi-tuong"'));
    }

    public function test_popup_co_du_ba_o_ten_sdt_doi_tuong(): void
    {
        $this->dungHaiNhom();

        $html = $this->get('/ho-so/can-chuan-bi')->assertOk()->getContent();

        $phanPopup = $this->catPopup($html);

        $this->assertMatchesRegularExpression('/<input[^>]*id="nx-hs-ten"[^>]*name="name"/', $phanPopup);
        $this->assertMatchesRegularExpression('/<input[^>]*id="nx-hs-sdt"[^>]*name="phone"/', $phanPopup);
        $this->assertMatchesRegularExpression('/<select[^>]*name="interest"/', $phanPopup);
        $this->assertStringContainsString('value="ho-so-tron-bo"', $phanPopup);
    }

    /**
     * O "Doi tuong" trong popup la danh sach nhom NGUOI duoc mua nha o xa hoi
     * (12-13 nhom), khong phai danh sach BO HO SO. Hai thu khac nhau: bo ho so
     * la nhom giay to, con day la dien chinh sach cua khach.
     *
     * Va no do DONG tu dap an buoc "Doi tuong" cua bo kiem tra dieu kien, de
     * nghi dinh doi thi quan tri sua mot cho la ca hai noi cung doi.
     */
    public function test_o_doi_tuong_trong_popup_la_nhom_chinh_sach_do_tu_csdl(): void
    {
        $this->dungHaiNhom();

        $phanPopup = $this->catPopup($this->get('/ho-so/can-chuan-bi')->assertOk()->getContent());

        $dapAn = $this->dapAnDoiTuong();

        if ($dapAn->isEmpty()) {
            $this->markTestSkipped('Chua chay NoxhEligibilityWizardSeeder nen chua co nhom doi tuong.');
        }

        $this->assertGreaterThanOrEqual(10, $dapAn->count(), 'Ô Đối tượng có quá ít nhóm');

        foreach ($dapAn as $nhan) {
            $this->assertStringContainsString(
                '<option value="' . e($nhan) . '"',
                $phanPopup,
                "Ô Đối tượng thiếu nhóm \"{$nhan}\""
            );
        }

        // Khong duoc lay nham danh sach bo ho so.
        $this->assertStringNotContainsString('Nhóm thử nghiệm A', $phanPopup);
    }

    /** Dap an cua buoc "Doi tuong" trong bo kiem tra dieu kien. */
    private function dapAnDoiTuong()
    {
        $cauHoi = DB::table('eligibility_questions')
            ->whereNull('deleted_at')
            ->where('publish', 2)
            ->where('question', 'LIKE', '%nhóm đối tượng%')
            ->value('id');

        if (!$cauHoi) {
            return collect();
        }

        return DB::table('eligibility_options')
            ->where('eligibility_question_id', $cauHoi)
            ->orderBy('order')
            ->orderBy('id')
            ->pluck('label');
    }

    /**
     * JS hong hay bi chan thi bam nut phai van tai duoc ho so. Doi href thanh
     * "#" la nguoi dung mat han duong tai.
     */
    public function test_khong_co_js_thi_nut_van_tai_duoc_ho_so(): void
    {
        $this->dungHaiNhom();

        $html = $this->get('/ho-so/can-chuan-bi')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '#<a href="[^"]*/ho-so/nhom-thu-a/tron-bo"\s+data-nx-tron-bo#',
            $html,
            'Nút không còn trỏ tới đường dẫn tải thật'
        );
    }

    public function test_popup_gui_bang_ajax_nhan_json_va_luu_thong_tin(): void
    {
        Http::fake();

        $this->dungHaiNhom();

        $this->postJson('/de-lai-thong-tin', [
            'name' => 'Khách tải trọn bộ',
            'phone' => '0911777999',
            'interest' => 'Nhóm thử nghiệm A',
            'source' => 'ho-so-tron-bo',
        ])
            ->assertOk()
            ->assertJsonPath('xong', true);

        $dong = DB::table('contacts')->where('phone', '0911777999')->latest('id')->first();

        $this->assertNotNull($dong, 'Không lưu được thông tin khách');
        $this->assertSame('ho-so-tron-bo', $dong->source);
        $this->assertSame('Nhóm thử nghiệm A', $dong->interest);
    }

    /**
     * Thieu so dien thoai thi phai tra loi kiem tra duoi dang JSON. Tra ve mot
     * trang HTML chuyen huong thi popup doc khong ra, nguoi dung thay nut bam
     * xong khong co gi xay ra.
     */
    public function test_popup_thieu_so_dien_thoai_thi_bao_loi_dang_json(): void
    {
        $this->postJson('/de-lai-thong-tin', [
            'name' => 'Khách thiếu số',
            'source' => 'ho-so-tron-bo',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }

    public function test_tin_telegram_cua_popup_goi_dung_ten_nguon_va_nhan_doi_tuong(): void
    {
        Http::fake();

        $this->datCaiDat('telegram_bot_token', '123456789:' . str_repeat('A', 35));
        $this->datCaiDat('telegram_chat_id', '123456');

        $this->postJson('/de-lai-thong-tin', [
            'name' => 'Khách tải trọn bộ',
            'phone' => '0911777999',
            'interest' => 'Nhóm thử nghiệm A',
            'source' => 'ho-so-tron-bo',
        ])->assertOk();

        Http::assertSent(function ($yeuCau) {
            $chu = $yeuCau['text'] ?? '';

            return str_contains($yeuCau->url(), 'api.telegram.org')
                && str_contains($chu, 'Đối tượng:')
                && !str_contains($chu, 'Quan tâm:')
                // Khong duoc de lo ma khoa noi bo 'ho-so-tron-bo' ra tin nhan.
                && str_contains($chu, 'popup tải trọn bộ')
                && !str_contains($chu, 'ho-so-tron-bo');
        });
    }

    /**
     * Chu trong popup phai sua duoc trong quan tri. Khoa dossier_modal_* va
     * dossier_form_* truoc day duoc goi trong giao dien nhung khong khai bao o
     * App\Classes\Introduce, nen khong bao gio hien ra o man hinh quan tri.
     */
    public function test_chu_trong_popup_sua_duoc_trong_quan_tri(): void
    {
        $o = (new \App\Classes\Introduce())->config()['dossier']['value'] ?? [];

        foreach (['form_heading', 'form_note', 'modal_heading', 'modal_note', 'modal_button'] as $khoa) {
            $this->assertArrayHasKey($khoa, $o, "Thiếu ô {$khoa} trong màn hình quản trị trang Hồ sơ");
        }
    }

    /** Cat rieng doan HTML cua popup de khong dem nham form o cuoi trang. */
    private function catPopup(string $html): string
    {
        $dau = strpos($html, 'data-nx-ho-so-modal');

        $this->assertNotFalse($dau, 'Không thấy popup trên trang');

        return substr($html, $dau);
    }

    /**
     * Duong dan {bo} khong duoc nuot mat cac trang co dinh cua muc Ho so.
     */
    public function test_cac_trang_ho_so_co_dinh_van_mo_duoc(): void
    {
        $this->get('/ho-so/mau-don')->assertOk();
        $this->get('/ho-so/checklist')->assertOk();
        $this->get('/ho-so/can-chuan-bi')->assertOk();
    }
}
