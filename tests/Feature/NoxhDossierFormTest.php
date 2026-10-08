<?php

namespace Tests\Feature;

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

    /** Hai nhom doi tuong tam, de bai kiem tra khong phu thuoc du lieu that. */
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
}
