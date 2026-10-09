<?php

namespace App\Console\Commands;

use App\Services\Noxh\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Kiểm tra và đặt cấu hình Telegram ngay trên máy chủ, không cần mở trang quản trị.
 *
 * Chạy:
 *   php artisan noxh:telegram                          -> kiểm tra token, liệt kê chat id
 *   php artisan noxh:telegram --gui-thu                -> gửi thêm một tin thử
 *   php artisan noxh:telegram --dat-token="123:ABC..." --dat-chat-id="987654321"
 *
 * Vì sao cần bản dòng lệnh: người quản trị máy chủ thường không muốn đăng nhập
 * trang quản trị chỉ để xem một thông báo lỗi, mà lỗi Telegram thì luôn xảy ra
 * đúng lúc đang cần gấp.
 *
 * CẤU HÌNH NẰM TRONG CSDL, KHÔNG NẰM TRONG .env: xem chu thich o
 * TelegramService - nguoi quan tri website khong dong vao duoc tep .env, nen
 * token de trong do thi ho khong tu doi duoc.
 */
class KiemTraTelegram extends Command
{
    protected $signature = 'noxh:telegram
        {--gui-thu : Gửi thử một tin tới chat id đã lưu}
        {--dat-token= : Lưu bot token mới vào cấu hình}
        {--dat-chat-id= : Lưu chat id mới vào cấu hình}';

    protected $description = 'Kiểm tra bot token, chat id và gửi thử thông báo Telegram';

    public function handle(TelegramService $telegram): int
    {
        $this->newLine();
        $this->info('=== Kiểm tra Telegram ===');

        $this->luuNeuCo();

        // ── Token ───────────────────────────────────────────────────────────
        $token = app(\App\Services\Noxh\TelegramService::class)->kiemTraToken();

        if ($token['ok']) {
            $this->line('  token      : <fg=green>hợp lệ</> — ' . $token['bot']);
        } else {
            $this->line('  token      : <fg=red>KHÔNG dùng được</> — ' . $token['loi']);
        }

        if (!empty($token['canhBao'])) {
            $this->line('  <fg=yellow>lưu ý      : ' . $token['canhBao'] . '</>');
        }

        if (!$token['ok']) {
            $this->newLine();
            $this->line('  Sửa: vào trang quản trị → Hệ thống → Cấu hình → Thông báo Telegram,');
            $this->line('  dán lại token lấy từ @BotFather (bấm /mybots → chọn bot → API Token).');

            return self::FAILURE;
        }

        // ── Chat id đang lưu ────────────────────────────────────────────────
        $phong = trim((string) cai_dat('telegram_chat_id', ''));
        $this->line('  chat id    : ' . ($phong !== '' ? $phong : '<fg=yellow>chưa nhập</>'));

        // ── Những chat đã từng nhắn cho bot ─────────────────────────────────
        $tim = $telegram->timChatId();

        if (!$tim['ok']) {
            $this->line('  tìm chat id: <fg=red>' . $tim['loi'] . '</>');
        } elseif (empty($tim['ds'])) {
            $this->line('  tìm chat id: <fg=yellow>chưa thấy chat nào</>');
            $this->line('               ' . $tim['goiY']);
        } else {
            $this->line('  tìm chat id: các chat đã nhắn cho bot —');
            foreach ($tim['ds'] as $c) {
                $this->line(sprintf('               %-16s %s (%s)', $c['id'], $c['ten'] ?: '(không tên)', $c['loai']));
            }
            $this->line('               Điền một id ở trên vào ô "Chat ID nhận thông báo".');
        }

        // ── Gửi thử ─────────────────────────────────────────────────────────
        if ($this->option('gui-thu')) {
            $this->newLine();
            $thu = $telegram->guiThu();

            if ($thu['ok']) {
                $this->line('  gửi thử    : <fg=green>đã gửi</> tới ' . $thu['den']);
            } else {
                $this->line('  gửi thử    : <fg=red>hỏng</> — ' . $thu['loi']);
                if (!empty($thu['goiY'])) {
                    $this->line('               ' . $thu['goiY']);
                }
            }
        }

        $this->newLine();

        return self::SUCCESS;
    }

    /** Lưu token / chat id nếu người chạy có truyền vào. */
    private function luuNeuCo(): void
    {
        $cap = [
            'telegram_bot_token' => $this->option('dat-token'),
            'telegram_chat_id' => $this->option('dat-chat-id'),
        ];

        foreach ($cap as $keyword => $giaTri) {
            if ($giaTri === null || $giaTri === '') {
                continue;
            }

            $this->luuCaiDat($keyword, trim((string) $giaTri));
            $this->line('  đã lưu     : ' . $keyword);
        }
    }

    /**
     * Ghi một khoá cấu hình vào bảng `systems`.
     *
     * Bảng này lưu theo (keyword, language_id): ghi vào ngôn ngữ mặc định để mọi
     * màn hình đều đọc được, và sửa dòng đã có chứ không thêm dòng mới - thêm
     * trùng thì `cai_dat()` chỉ đọc được một dòng và không ai biết dòng kia.
     */
    private function luuCaiDat(string $keyword, string $giaTri): void
    {
        $coSan = DB::table('systems')->where('keyword', $keyword)->exists();

        if ($coSan) {
            DB::table('systems')->where('keyword', $keyword)->update([
                'content' => $giaTri,
                'updated_at' => now(),
            ]);

            return;
        }

        DB::table('systems')->insert([
            'keyword' => $keyword,
            'content' => $giaTri,
            'language_id' => DB::table('languages')->where('canonical', 'vn')->value('id') ?? 1,
            'user_id' => DB::table('users')->min('id') ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
