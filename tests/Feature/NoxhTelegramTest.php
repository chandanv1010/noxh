<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Noxh\TelegramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Cau hinh Telegram va ba nut kiem tra trong trang quan tri.
 *
 * VI SAO CAN:
 * Cau hinh Telegram sai thi bieu hien duy nhat la "khong thay thong bao nao ve
 * may". Co it nhat bon nguyen nhan khac nhau cho cung bieu hien do, va cach sua
 * moi cai mot khac. Bai kiem tra nay ghim lai tung truong hop, dac biet la
 * truong hop token bi dan THIEU KY TU - Telegram chi tra ve "Unauthorized"
 * chung chung nen nguoi dung khong tai nao doan ra.
 */
class NoxhTelegramTest extends TestCase
{
    private array $caiDatCu = [];

    protected function tearDown(): void
    {
        foreach ($this->caiDatCu as $keyword => $cu) {
            if ($cu === null) {
                DB::table('systems')->where('keyword', $keyword)->delete();
            } else {
                DB::table('systems')->where('keyword', $keyword)->update(['content' => $cu]);
            }
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

    private function quanTri(): ?User
    {
        return User::whereHas('user_catalogues.permissions')->where('publish', 2)->first();
    }

    /** Token that cua Telegram co DUNG 35 ky tu sau dau hai cham. */
    private function tokenDung(): string
    {
        return '123456789:' . str_repeat('A', 35);
    }

    public function test_man_hinh_cau_hinh_co_khoi_kiem_tra_telegram(): void
    {
        $qt = $this->quanTri();

        if (!$qt) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        $html = $this->actingAs($qt)->get('/system/index')->assertOk()->getContent();

        $this->assertStringContainsString('telegram-kiem-tra', $html);
        $this->assertStringContainsString('data-tg="token"', $html);
        $this->assertStringContainsString('data-tg="chat-id"', $html);
        $this->assertStringContainsString('data-tg="gui-thu"', $html);
        $this->assertStringContainsString('config[telegram_bot_token]', $html);
        $this->assertStringContainsString('config[telegram_chat_id]', $html);
    }

    public function test_token_dan_thieu_ky_tu_thi_bao_dung_cho_sai(): void
    {
        // Dung 34 ky tu - dung tinh huong da gap that: dan tu BotFather bi mat
        // mot ky tu, Telegram tra ve 401 Unauthorized khong noi gi them.
        $this->datCaiDat('telegram_bot_token', '123456789:' . str_repeat('A', 34));
        $this->datCaiDat('telegram_chat_id', '123456');

        $kq = app(TelegramService::class)->kiemTraToken();

        $this->assertFalse($kq['ok']);
        $this->assertStringContainsString('34', $kq['canhBao'] ?? '');
        $this->assertStringContainsString('35', $kq['canhBao'] ?? '');
    }

    public function test_token_dung_thi_khong_canh_bao_gi(): void
    {
        $this->datCaiDat('telegram_bot_token', $this->tokenDung());

        Http::fake([
            'api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => ['first_name' => 'Bot Của Tôi', 'username' => 'botcuatoi_bot'],
            ]),
        ]);

        $kq = app(TelegramService::class)->kiemTraToken();

        $this->assertTrue($kq['ok']);
        $this->assertNull($kq['canhBao'] ?? null);
        $this->assertStringContainsString('botcuatoi_bot', $kq['bot']);
    }

    public function test_tim_chat_id_doc_dung_get_updates(): void
    {
        $this->datCaiDat('telegram_bot_token', $this->tokenDung());

        Http::fake([
            'api.telegram.org/*' => Http::response([
                'ok' => true,
                'result' => [
                    ['message' => ['chat' => ['id' => 987654321, 'first_name' => 'Tuấn', 'type' => 'private']]],
                    ['message' => ['chat' => ['id' => -1001234567890, 'title' => 'Nhóm NOXH', 'type' => 'supergroup']]],
                ],
            ]),
        ]);

        $kq = app(TelegramService::class)->timChatId();

        $this->assertTrue($kq['ok']);
        $this->assertCount(2, $kq['ds']);
        $this->assertSame('987654321', $kq['ds'][0]['id']);
        $this->assertSame('-1001234567890', $kq['ds'][1]['id']);
        $this->assertSame('Nhóm NOXH', $kq['ds'][1]['ten']);
    }

    public function test_chua_ai_nhan_bot_thi_goi_y_cach_lam(): void
    {
        $this->datCaiDat('telegram_bot_token', $this->tokenDung());

        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);

        $kq = app(TelegramService::class)->timChatId();

        $this->assertTrue($kq['ok']);
        $this->assertSame([], $kq['ds']);
        $this->assertStringContainsString('nhắn', $kq['goiY'] ?? '');
    }

    public function test_chat_not_found_thi_goi_y_phan_biet_hai_nguyen_nhan(): void
    {
        $this->datCaiDat('telegram_bot_token', $this->tokenDung());
        $this->datCaiDat('telegram_chat_id', '999999999');

        Http::fake([
            'api.telegram.org/*' => Http::response([
                'ok' => false,
                'error_code' => 400,
                'description' => 'Bad Request: chat not found',
            ], 400),
        ]);

        $kq = app(TelegramService::class)->guiThu();

        $this->assertFalse($kq['ok']);
        $this->assertStringContainsString('chat not found', $kq['loi']);
        $this->assertNotNull($kq['goiY'] ?? null);
        $this->assertStringContainsString('Start', $kq['goiY']);
    }

    public function test_thieu_cau_hinh_thi_noi_ro_thieu_gi(): void
    {
        $this->datCaiDat('telegram_bot_token', '');
        $this->datCaiDat('telegram_chat_id', '');

        $dv = app(TelegramService::class);

        $this->assertFalse($dv->batDuoc());
        $this->assertStringContainsString('bot token', $dv->kiemTraToken()['loi']);
        $this->assertStringContainsString('bot token', $dv->guiThu()['loi']);
    }

    public function test_khong_phai_ajax_thi_khong_lo_du_lieu(): void
    {
        $qt = $this->quanTri();

        if (!$qt) {
            $this->markTestSkipped('Không có tài khoản quản trị nào.');
        }

        // Goi thang bang trinh duyet (khong phai ajax) thi phai quay ve trang
        // cau hinh, khong tra JSON ra giua trang.
        $this->actingAs($qt)
            ->post('/system/telegram/kiem-tra', ['viec' => 'token'])
            ->assertRedirect(route('system.index'));
    }

    /**
     * Dat cau hinh Telegram bang dong lenh, khong can mo trang quan tri.
     *
     * Cau hinh nam trong bang `systems` chu KHONG nam trong .env, nen tren may
     * chu nguoi quan tri phai co duong dat ma khong phai dong vao tep .env.
     */
    public function test_dat_cau_hinh_bang_dong_lenh(): void
    {
        // Lenh nay vua luu cau hinh vua KIEM TRA token, va tra ma loi khac 0 khi
        // token hong. Gia lap Telegram tra loi tot de bai kiem tra di het duoc
        // duong di tot, chu khong phai de che loi.
        Http::fake([
            'api.telegram.org/*/getMe' => Http::response([
                'ok' => true,
                'result' => ['first_name' => 'Bot Thử', 'username' => 'botthu_bot'],
            ]),
            'api.telegram.org/*/getUpdates' => Http::response(['ok' => true, 'result' => []]),
        ]);

        $tokenA = '123456789:' . str_repeat('A', 35);
        $tokenB = '123456789:' . str_repeat('B', 35);

        // Ghi nhan gia tri cu de tearDown tra lai.
        foreach (['telegram_bot_token', 'telegram_chat_id'] as $k) {
            $this->datCaiDat($k, (string) DB::table('systems')->where('keyword', $k)->value('content'));
        }

        $this->artisan('noxh:telegram', [
            '--dat-token' => $tokenA,
            '--dat-chat-id' => '987654321',
        ])->assertExitCode(0);

        $this->assertSame($tokenA, DB::table('systems')->where('keyword', 'telegram_bot_token')->value('content'));
        $this->assertSame('987654321', DB::table('systems')->where('keyword', 'telegram_chat_id')->value('content'));

        // Chay lan hai voi token khac: phai SUA dong cu. Them dong trung thi
        // cai_dat() chi doc duoc mot dong va khong ai biet dong kia.
        $this->artisan('noxh:telegram', ['--dat-token' => $tokenB])->assertExitCode(0);

        $this->assertSame(1, DB::table('systems')->where('keyword', 'telegram_bot_token')->count());
        $this->assertSame($tokenB, DB::table('systems')->where('keyword', 'telegram_bot_token')->value('content'));
    }
}
