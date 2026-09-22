<?php

namespace Tests\Feature;

use App\Models\EligibilityCheck;
use App\Models\EligibilityOption;
use App\Models\EligibilityQuestion;
use App\Models\QaQuestion;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Kiem tra frontend NOXH: cac trang mo duoc, bo loc chay dung, luong kiem tra
 * dieu kien cham diem va luu ket qua, cac form gui duoc.
 *
 * Khong dung RefreshDatabase: chay tren chinh CSDL dang phat trien. Ban ghi
 * tao ra deu tu don o cuoi moi ham.
 */
class NoxhFrontendTest extends TestCase
{
    public static function trangCong(): array
    {
        return [
            'trang chu' => ['/'],
            'danh sach du an' => ['/du-an'],
            'du an theo tinh' => ['/du-an/tinh-thanh'],
            'kiem tra dieu kien' => ['/kiem-tra-dieu-kien'],
            'bo cau hoi' => ['/kiem-tra-dieu-kien/cau-hoi'],
            'phong phap ly' => ['/phap-ly-noxh'],
            'van ban phap luat' => ['/phap-ly-noxh/van-ban'],
            'ho so can chuan bi' => ['/ho-so/can-chuan-bi'],
            'mau don' => ['/ho-so/mau-don'],
            'checklist' => ['/ho-so/checklist'],
            'tinh khoan vay' => ['/tai-chinh/tinh-khoan-vay'],
            'kha nang tai chinh' => ['/tai-chinh/kha-nang-tai-chinh'],
            'hoi dap' => ['/hoi-dap'],
            'tin tuc' => ['/tin-tuc'],
        ];
    }

    /** @dataProvider trangCong */
    public function test_trang_mo_duoc(string $uri): void
    {
        $this->get($uri)->assertOk();
    }

    public function test_trang_chu_hien_du_an_va_so_lieu(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('NOXH Túc Duyên', $html, 'Trang chu khong hien du an noi bat');
        $this->assertStringContainsString('120+', $html, 'Trang chu khong hien dai so lieu');
        // So phai de trong data-nx-dem thi JS moi dem tu 0 len duoc.
        $this->assertStringContainsString('data-nx-dem', $html);
    }

    public function test_banner_trang_chu_co_hai_ban_anh(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $mobile = DB::table('introduces')->where('keyword', 'hero_image_mobile')->value('content');

        if (!$mobile) {
            $this->markTestSkipped('Chưa cấu hình ảnh banner bản điện thoại.');
        }

        // Dung <picture> nen trinh duyet chi tai DUNG mot anh no can. Hai the
        // <img> an/hien bang CSS thi tai ca hai.
        $this->assertStringContainsString('<picture>', $html);
        $this->assertStringContainsString('media="(max-width: 1024px)"', $html);
        $this->assertStringContainsString($mobile, $html, 'Thiếu ảnh banner bản điện thoại');
    }

    public function test_chi_tiet_du_an_hien_tien_do_va_phap_ly(): void
    {
        $html = $this->get('/du-an/noxh-tuc-duyen')->assertOk()->getContent();

        $this->assertStringContainsString('NOXH Túc Duyên', $html);
        $this->assertStringContainsString('Khởi công xây dựng', $html, 'Thieu khoi tien do');
        $this->assertStringContainsString('Quyết định chủ trương đầu tư', $html, 'Thieu khoi phap ly');
        $this->assertStringContainsString('19,55 - 23,99', $html, 'Khoang gia hien sai dinh dang');
    }

    public function test_du_an_khong_ton_tai_thi_404(): void
    {
        $this->get('/du-an/khong-co-du-an-nay')->assertNotFound();
    }

    public function test_loc_theo_trang_thai_chi_ra_dung_du_an(): void
    {
        // "Da ban giao" chi co NOXH Song Cong trong du lieu mau.
        $html = $this->get('/du-an?status[]=handed')->assertOk()->getContent();

        $this->assertStringContainsString('NOXH Sông Công', $html);
        $this->assertStringNotContainsString('NOXH Phúc Thịnh', $html, 'Loc trang thai van lot du an khac');
    }

    public function test_loc_theo_khoang_gia_lay_ca_du_an_giao_nhau(): void
    {
        // NOXH Tuc Duyen co gia 19,55 - 23,99. Muc loc "20 - 22" phai lay no
        // vi hai khoang GIAO NHAU, du khoang cua du an khong nam gon ben trong.
        $html = $this->get('/du-an?gia[]=20-22')->assertOk()->getContent();

        $this->assertStringContainsString('NOXH Túc Duyên', $html,
            'Du an co khoang gia phu len muc loc nhung bi bo qua');
    }

    public function test_tim_kiem_theo_ten(): void
    {
        $html = $this->get('/du-an?tu-khoa=Evergreen')->assertOk()->getContent();

        $this->assertStringContainsString('NOXH Evergreen Bắc Giang', $html);
        $this->assertStringNotContainsString('NOXH Phúc Thịnh', $html);
    }

    public function test_lam_bai_kiem_tra_dieu_kien_va_nhan_ket_qua(): void
    {
        $cauHoi = EligibilityQuestion::with('options')->where('publish', 2)->orderBy('order')->get();

        if (!$cauHoi->count()) {
            $this->markTestSkipped('Chua co cau hoi dieu kien nao trong CSDL.');
        }

        // Chon dap an "dat" cho moi cau de ket qua ra muc cao.
        $traLoi = [];
        foreach ($cauHoi as $ch) {
            $dat = $ch->options->firstWhere('verdict', 'pass') ?? $ch->options->first();
            if ($dat) {
                $traLoi[$ch->id] = $dat->value;
            }
        }

        $response = $this->post('/kiem-tra-dieu-kien/cau-hoi', [
            'name' => 'Nguoi thu nghiem tu dong',
            'phone' => '0900000001',
            'traLoi' => $traLoi,
        ]);

        $luot = EligibilityCheck::where('phone', '0900000001')->latest('id')->first();
        $this->assertNotNull($luot, 'Khong luu duoc luot kiem tra');

        $response->assertRedirect(route('noxh.check.result', $luot->code));

        // Ma tra cuu phai co han 30 ngay.
        $this->assertNotNull($luot->expires_at);
        $this->assertTrue($luot->conHan(), 'Ma tra cuu vua tao ma da het han');

        // Cham diem phai luu lai tung cau tra loi.
        $this->assertSame(count($traLoi), $luot->answers()->count());
        $this->assertGreaterThan(0, $luot->score_percent, 'Tra loi dat het ma diem van bang 0');

        // Trang ket qua doc duoc bang ma.
        $html = $this->get('/kiem-tra-dieu-kien/ket-qua/' . $luot->code)->assertOk()->getContent();
        $this->assertStringContainsString($luot->code, $html);
        $this->assertStringContainsString('Chi tiết kết quả', $html);

        // Tra cuu lai bang ma.
        $this->post('/kiem-tra-dieu-kien/tra-cuu', ['code' => $luot->code])
            ->assertRedirect(route('noxh.check.result', $luot->code));

        $luot->answers()->delete();
        $luot->delete();
    }

    public function test_ket_qua_khong_ton_tai_thi_404(): void
    {
        $this->get('/kiem-tra-dieu-kien/ket-qua/NOXH-KHONG-CO')->assertNotFound();
    }

    public function test_de_lai_thong_tin_luu_vao_bang_lien_he(): void
    {
        $this->post('/de-lai-thong-tin', [
            'name' => 'Khach thu nghiem tu dong',
            'phone' => '0900000002',
            'source' => 'newsletter',
        ])->assertRedirect();

        $dong = DB::table('contacts')->where('phone', '0900000002')->first();
        $this->assertNotNull($dong, 'Khong luu duoc thong tin lien he');
        $this->assertSame('newsletter', $dong->source, 'Khong ghi lai form nao gui len');
        $this->assertSame('new', $dong->status);

        DB::table('contacts')->where('id', $dong->id)->delete();
    }

    public function test_thieu_so_dien_thoai_thi_bao_loi(): void
    {
        $this->post('/de-lai-thong-tin', ['name' => 'Chi co ten'])
            ->assertSessionHasErrors('phone');
    }

    public function test_gui_cau_hoi_mac_dinh_la_an_cho_duyet(): void
    {
        $this->post('/hoi-dap/gui-cau-hoi', [
            'title' => 'Cau hoi thu nghiem tu dong?',
            'asker_name' => 'Nguoi hoi thu nghiem',
        ])->assertRedirect();

        $ch = QaQuestion::where('title', 'Cau hoi thu nghiem tu dong?')->first();
        $this->assertNotNull($ch);

        // Cau hoi moi KHONG duoc hien ra ngoai truoc khi duyet.
        $this->assertSame(1, (int) $ch->publish, 'Cau hoi moi gui da hien ra ngoai website');
        $this->assertSame('pending', $ch->status);

        $this->get('/hoi-dap')->assertOk()->assertDontSee('Cau hoi thu nghiem tu dong?');

        $ch->forceDelete();
    }

    public function test_tai_van_ban_thi_dem_luot_tai(): void
    {
        $vb = DB::table('legal_documents')->where('publish', 2)->first();

        if (!$vb) {
            $this->markTestSkipped('Chua co van ban nao.');
        }

        DB::table('legal_documents')->where('id', $vb->id)->update([
            'file' => 'https://example.com/van-ban.pdf',
        ]);

        $truoc = (int) DB::table('legal_documents')->where('id', $vb->id)->value('download_count');

        $this->get('/phap-ly-noxh/van-ban/' . $vb->id . '/tai-ve')
            ->assertRedirect('https://example.com/van-ban.pdf');

        $sau = (int) DB::table('legal_documents')->where('id', $vb->id)->value('download_count');
        $this->assertSame($truoc + 1, $sau, 'Khong dem luot tai');

        DB::table('legal_documents')->where('id', $vb->id)->update([
            'file' => $vb->file,
            'download_count' => $truoc,
        ]);
    }
}
