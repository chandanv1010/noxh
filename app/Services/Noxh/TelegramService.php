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
}
