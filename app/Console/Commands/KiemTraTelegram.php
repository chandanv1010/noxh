<?php

namespace App\Console\Commands;

use App\Services\Noxh\TelegramService;
use Illuminate\Console\Command;

/**
 * Kiểm tra cấu hình Telegram ngay trên máy chủ, không cần mở trang quản trị.
 *
 * Chạy:
 *   php artisan noxh:telegram            -> kiểm tra token và liệt kê chat id
 *   php artisan noxh:telegram --gui-thu  -> gửi thêm một tin thử
 *
 * Vì sao cần bản dòng lệnh: người quản trị máy chủ thường không muốn đăng nhập
 * trang quản trị chỉ để xem một thông báo lỗi, mà lỗi Telegram thì luôn xảy ra
 * đúng lúc đang cần gấp.
 */
class KiemTraTelegram extends Command
{
    protected $signature = 'noxh:telegram {--gui-thu : Gửi thử một tin tới chat id đã lưu}';

    protected $description = 'Kiểm tra bot token, chat id và gửi thử thông báo Telegram';

    public function handle(TelegramService $telegram): int
    {
        $this->newLine();
        $this->info('=== Kiểm tra Telegram ===');

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
}
