<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserCatalogue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Nut "Lien he" o tung nhan vien kinh doanh.
 *
 * Hai diem quan trong:
 *   - ban ghi phai duoc gan dung nguoi khach bam vao (contacts.assigned_user_id)
 *   - Telegram gui hong thi KHONG duoc lam hong viec luu. Khach de lai so dien
 *     thoai thi thu phai luu la ban ghi trong CSDL.
 */
class NoxhAdvisorContactTest extends TestCase
{
    private ?User $sale = null;
    private ?UserCatalogue $nhom = null;
    private array $caiDatCu = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->nhom = UserCatalogue::firstOrCreate(
            ['name' => 'Nhóm sale thử nghiệm liên hệ'],
            ['is_sale' => 1, 'publish' => 2]
        );
        $this->nhom->update(['is_sale' => 1, 'publish' => 2]);

        DB::table('users')->where('email', 'sale.lienhe@example.com')->delete();

        $this->sale = User::create([
            'name' => 'Tư vấn viên thử nghiệm',
            'title' => 'Chuyên viên tư vấn',
            'email' => 'sale.lienhe@example.com',
            'password' => Hash::make('matkhau@123'),
            'phone' => '0900000300',
            'user_catalogue_id' => $this->nhom->id,
            'publish' => 2,
        ]);
    }

    protected function tearDown(): void
    {
        DB::table('contacts')->where('phone', '0911222333')->delete();

        foreach ($this->caiDatCu as $keyword => $cu) {
            if ($cu === null) {
                DB::table('systems')->where('keyword', $keyword)->delete();
            } else {
                DB::table('systems')->where('keyword', $keyword)->update(['content' => $cu]);
            }
        }

        if ($this->sale) {
            DB::table('users')->where('id', $this->sale->id)->delete();
        }

        if ($this->nhom) {
            DB::table('user_catalogues')->where('id', $this->nhom->id)->delete();
        }

        parent::tearDown();
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

    private function guiYeuCau(array $them = [])
    {
        return $this->postJson(route('noxh.lead.advisor'), array_merge([
            'name' => 'Khách thử nghiệm tự động',
            'phone' => '0911222333',
            'message' => 'Tôi muốn hỏi về hồ sơ.',
            'nhan_vien_id' => $this->sale->id,
        ], $them));
    }

    public function test_gui_duoc_va_gan_dung_nhan_vien(): void
    {
        Http::fake();

        $this->guiYeuCau()->assertOk()->assertJson(['xong' => true]);

        $dong = DB::table('contacts')->where('phone', '0911222333')->latest('id')->first();

        $this->assertNotNull($dong, 'Không lưu được thông tin liên hệ');
        $this->assertSame($this->sale->id, (int) $dong->assigned_user_id, 'Không gán đúng nhân viên');
        $this->assertSame('advisor', $dong->source);
        $this->assertSame('new', $dong->status);
        $this->assertSame('Tôi muốn hỏi về hồ sơ.', $dong->message);
    }

    public function test_thieu_so_dien_thoai_thi_bao_loi(): void
    {
        $this->postJson(route('noxh.lead.advisor'), [
            'name' => 'Chỉ có tên',
            'nhan_vien_id' => $this->sale->id,
        ])->assertStatus(422)->assertJsonValidationErrors('phone');
    }

    public function test_khong_gui_duoc_cho_nguoi_khong_phai_nhan_vien_kinh_doanh(): void
    {
        // id gui len tu trinh duyet nen khong tin duoc: doi sang id cua mot
        // quan tri vien thi phai bi tu choi.
        $quanTri = User::whereHas('user_catalogues', fn ($q) => $q->where('is_sale', 0))->first();

        if (!$quanTri) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        $this->guiYeuCau(['nhan_vien_id' => $quanTri->id])->assertStatus(422);

        $this->assertNull(
            DB::table('contacts')->where('phone', '0911222333')->first(),
            'Vẫn lưu dù người nhận không phải nhân viên kinh doanh'
        );
    }

    public function test_nhan_vien_bi_khoa_thi_tu_choi(): void
    {
        $this->sale->update(['publish' => 1]);

        $this->guiYeuCau()->assertStatus(422);
    }

    public function test_telegram_nhan_duoc_tin(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

        $this->datCaiDat('telegram_bot_token', '123456:THU-NGHIEM');
        $this->datCaiDat('telegram_chat_id', '-1001234567890');

        $this->guiYeuCau()->assertOk();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.telegram.org/bot123456:THU-NGHIEM/sendMessage')
                && str_contains($request['text'], '0911222333')
                && str_contains($request['text'], 'Tư vấn viên thử nghiệm');
        });
    }

    public function test_telegram_hong_thi_van_luu_duoc_thong_tin(): void
    {
        // Diem mau chot: khach de lai so dien thoai thi thu phai luu la ban ghi
        // trong CSDL. Telegram chi la tien cho quan tri biet som.
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false], 500)]);

        $this->datCaiDat('telegram_bot_token', '123456:THU-NGHIEM');
        $this->datCaiDat('telegram_chat_id', '-1001234567890');

        $this->guiYeuCau()->assertOk()->assertJson(['xong' => true]);

        $this->assertNotNull(
            DB::table('contacts')->where('phone', '0911222333')->first(),
            'Telegram hỏng làm mất luôn thông tin khách'
        );
    }

    public function test_chua_cau_hinh_telegram_thi_khong_goi_di_dau(): void
    {
        Http::fake();

        $this->datCaiDat('telegram_bot_token', '');
        $this->datCaiDat('telegram_chat_id', '');

        $this->guiYeuCau()->assertOk();

        Http::assertNothingSent();
    }

    public function test_khoi_tu_van_hien_ra_trang_chu(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Tư vấn hồ sơ tại khu vực của bạn', $html);
        $this->assertStringContainsString('Tư vấn viên thử nghiệm', $html);
        $this->assertStringContainsString('data-nx-lien-he="' . $this->sale->id . '"', $html);

        // Popup phai co mat, khong thi nut bam khong ra gi.
        $this->assertStringContainsString('data-nx-modal', $html);
    }
}
