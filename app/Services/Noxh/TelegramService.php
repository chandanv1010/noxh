<?php

namespace App\Services\Noxh;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gui thong bao ve Telegram cua quan tri.
 *
 * Bot token va chat id do quan tri tu dien trong Cau hinh he thong - khong
 * ghi cung vao ma nguon, va cung khong de trong .env vi nguoi quan tri website
 * khong dong vao file do duoc.
 *
 * Nguyen tac quan trong: gui that bai thi KHONG duoc lam hong viec chinh. Khach
 * de lai so dien thoai thi thu phai luu la ban ghi trong CSDL; Telegram chi la
 * tien cho quan tri biet som. Mang chap chon hay token sai deu chi ghi vao
 * nhat ky roi thoi.
 */
class TelegramService
{
    /** Bao lau thi bo cuoc. De ngan vi nguoi dung dang doi trang tra ve. */
    private const CHO_GIAY = 5;

    public function batDuoc(): bool
    {
        return $this->token() !== '' && $this->phong() !== '';
    }

    /**
     * Gui mot doan van ban. Tra ve true khi Telegram nhan.
     */
    public function gui(string $noiDung): bool
    {
        if (!$this->batDuoc()) {
            return false;
        }

        try {
            $tra = Http::timeout(self::CHO_GIAY)
                ->asForm()
                ->post('https://api.telegram.org/bot' . $this->token() . '/sendMessage', [
                    'chat_id' => $this->phong(),
                    'text' => $noiDung,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            if (!$tra->successful()) {
                Log::warning('Telegram tu choi tin nhan: ' . $tra->body());
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Khong gui duoc Telegram: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Tin bao co khach de lai thong tin.
     *
     * @param  array  $dong  Cac cap [nhan => gia tri]; dong nao rong thi bo qua
     */
    public function baoLienHe(string $tieuDe, array $dong): bool
    {
        $chu = '<b>' . e($tieuDe) . '</b>';

        foreach ($dong as $nhan => $giaTri) {
            if ($giaTri === null || $giaTri === '') {
                continue;
            }

            $chu .= "\n" . e($nhan) . ': <b>' . e((string) $giaTri) . '</b>';
        }

        $chu .= "\n\n" . e(config('app.url'));

        return $this->gui($chu);
    }

    private function token(): string
    {
        return trim((string) cai_dat('telegram_bot_token', ''));
    }

    private function phong(): string
    {
        return trim((string) cai_dat('telegram_chat_id', ''));
    }

    // ── Phần dưới đây chỉ dùng cho nút kiểm tra trong trang quản trị ─────────
    //
    // Vì sao cần: cấu hình Telegram sai thì biểu hiện duy nhất là "không thấy
    // thông báo nào về máy" — mà có ít nhất bốn nguyên nhân khác nhau (token
    // sai, token thiếu ký tự, chat id sai, bot chưa từng được nhắn). Đoán mò
    // rất mất thời gian, nên phải có chỗ bấm ra câu trả lời dứt khoát.

    /** Gọi getMe để biết token có thật sự dùng được không. */
    public function kiemTraToken(): array
    {
        $token = $this->token();

        if ($token === '') {
            return ['ok' => false, 'loi' => 'Chưa nhập bot token.'];
        }

        // Token của Telegram có ĐÚNG 35 ký tự sau dấu hai chấm. Sai độ dài gần
        // như chắc chắn là dán thiếu hoặc dán thừa, mà Telegram chỉ trả về
        // "Unauthorized" chung chung nên rất khó đoán ra.
        $sauHaiCham = strlen(substr($token, strpos($token, ':') === false ? 0 : strpos($token, ':') + 1));
        $canhBao = null;

        if (strpos($token, ':') === false || $sauHaiCham !== 35) {
            $canhBao = 'Token này có ' . $sauHaiCham . ' ký tự sau dấu hai chấm, còn token hợp lệ phải đúng 35. '
                . 'Nhiều khả năng đã dán thiếu một ký tự — hãy chép lại từ @BotFather.';
        }

        try {
            $tra = Http::timeout(self::CHO_GIAY)->get('https://api.telegram.org/bot' . $token . '/getMe');
            $du = $tra->json();

            if (!($du['ok'] ?? false)) {
                return [
                    'ok' => false,
                    'loi' => 'Telegram từ chối token: ' . ($du['description'] ?? ('HTTP ' . $tra->status())),
                    'canhBao' => $canhBao,
                ];
            }

            return [
                'ok' => true,
                'bot' => ($du['result']['first_name'] ?? '') . ' (@' . ($du['result']['username'] ?? '') . ')',
                'canhBao' => $canhBao,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'loi' => 'Không gọi được Telegram: ' . $e->getMessage(), 'canhBao' => $canhBao];
        }
    }

    /**
     * Liệt kê các chat đã từng nhắn cho bot, kèm id.
     *
     * Đây là cách lấy chat id chắc chắn nhất: nhắn một câu bất kỳ cho bot rồi
     * bấm nút này. Cách "nhắn @userinfobot" hay được chỉ dẫn KHÔNG dùng được
     * cho bot vì id người dùng và id chat có thể khác nhau.
     */
    public function timChatId(): array
    {
        $token = $this->token();

        if ($token === '') {
            return ['ok' => false, 'loi' => 'Chưa nhập bot token.'];
        }

        try {
            $tra = Http::timeout(self::CHO_GIAY)->get('https://api.telegram.org/bot' . $token . '/getUpdates');
            $du = $tra->json();

            if (!($du['ok'] ?? false)) {
                return ['ok' => false, 'loi' => $du['description'] ?? ('HTTP ' . $tra->status())];
            }

            $thay = [];
            $ds = [];

            foreach (($du['result'] ?? []) as $capNhat) {
                $tin = $capNhat['message'] ?? $capNhat['edited_message'] ?? $capNhat['channel_post'] ?? null;

                if (!$tin || !isset($tin['chat']['id'])) {
                    continue;
                }

                $id = (string) $tin['chat']['id'];

                if (isset($thay[$id])) {
                    continue;
                }

                $thay[$id] = true;
                $ds[] = [
                    'id' => $id,
                    'ten' => $tin['chat']['title'] ?? trim(($tin['chat']['first_name'] ?? '') . ' ' . ($tin['chat']['last_name'] ?? '')),
                    'loai' => $tin['chat']['type'] ?? '',
                ];
            }

            return [
                'ok' => true,
                'ds' => $ds,
                'goiY' => $ds
                    ? null
                    : 'Chưa thấy tin nào. Hãy mở Telegram, nhắn một câu bất kỳ cho bot rồi bấm lại nút này.',
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'loi' => 'Không gọi được Telegram: ' . $e->getMessage()];
        }
    }

    /** Gửi một tin thử và trả về LÝ DO khi hỏng, không chỉ true/false. */
    public function guiThu(): array
    {
        if ($this->token() === '') {
            return ['ok' => false, 'loi' => 'Chưa nhập bot token.'];
        }

        if ($this->phong() === '') {
            return ['ok' => false, 'loi' => 'Chưa nhập chat id.'];
        }

        try {
            $tra = Http::timeout(self::CHO_GIAY)
                ->asForm()
                ->post('https://api.telegram.org/bot' . $this->token() . '/sendMessage', [
                    'chat_id' => $this->phong(),
                    'text' => "Tin thử từ " . config('app.url') . "\nCấu hình Telegram đã đúng.",
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]);

            $du = $tra->json();

            if (!($du['ok'] ?? false)) {
                $mo = $du['description'] ?? ('HTTP ' . $tra->status());
                $goiY = null;

                // Hai lỗi này gặp nhiều nhất và cách sửa không hiển nhiên.
                if (stripos((string) $mo, 'chat not found') !== false) {
                    $goiY = 'Chat id đúng nhưng bot chưa từng nói chuyện với người/nhóm đó. '
                        . 'Mở Telegram, tìm bot rồi bấm Start (hoặc thêm bot vào nhóm và nhắn một câu) trước đã.';
                } elseif (stripos((string) $mo, 'bot was blocked') !== false || stripos((string) $mo, 'user is deactivated') !== false) {
                    $goiY = 'Người nhận đã chặn bot. Bỏ chặn rồi thử lại.';
                }

                return ['ok' => false, 'loi' => $mo, 'goiY' => $goiY];
            }

            return ['ok' => true, 'den' => $du['result']['chat']['title'] ?? ($du['result']['chat']['first_name'] ?? $this->phong())];
        } catch (\Throwable $e) {
            return ['ok' => false, 'loi' => 'Không gọi được Telegram: ' . $e->getMessage()];
        }
    }
}
