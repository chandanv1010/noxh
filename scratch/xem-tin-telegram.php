<?php

/**
 * In ra ĐÚNG nội dung tin nhắn Telegram mà từng form sẽ gửi.
 *
 * Chạy: php scratch/xem-tin-telegram.php
 *
 * Vì sao cần: đọc mã nguồn rồi tự suy ra tin nhắn trông thế nào là đoán. Script
 * này gọi thẳng service, chặn HTTP lại, rồi in ra phần thân yêu cầu — tức là
 * đúng chuỗi sẽ bay sang Telegram.
 *
 * KHÔNG gửi gì ra ngoài: Http::fake() chặn hết.
 * KHÔNG cần token thật: tự đặt token giả rồi xoá lại sau khi chạy.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Noxh\TelegramService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// ── Đặt token giả để service chịu gửi, nhớ lại giá trị cũ để trả về ──────────
$khoa = ['telegram_bot_token', 'telegram_chat_id'];
$cu = [];

foreach ($khoa as $k) {
    $cu[$k] = DB::table('systems')->where('keyword', $k)->value('content');

    if ($cu[$k] === null) {
        DB::table('systems')->insert([
            'keyword' => $k,
            'content' => $k === 'telegram_bot_token' ? '123456789:' . str_repeat('A', 35) : '123456',
            'language_id' => DB::table('languages')->where('canonical', 'vn')->value('id') ?? 1,
            'user_id' => DB::table('users')->min('id') ?? 1,
        ]);
    } else {
        DB::table('systems')->where('keyword', $k)->update([
            'content' => $k === 'telegram_bot_token' ? '123456789:' . str_repeat('A', 35) : '123456',
        ]);
    }
}

/** Dựng lại tin nhắn y như LeadController::baoTelegram làm, rồi in ra. */
function inTin(string $tieuDe, array $dong): void
{
    Http::fake();

    app(TelegramService::class)->baoLienHe($tieuDe, $dong);

    $da = Http::recorded();

    if (!$da->count()) {
        echo "  (không gửi được - thiếu cấu hình)\n";

        return;
    }

    [$yeuCau] = $da->first();

    // Telegram nhận HTML; bo the cho de doc tren man hinh dong lenh.
    echo "  " . str_replace(['<b>', '</b>'], '', $yeuCau['text'] ?? '(không có nội dung)') . "\n";
}

try {
    echo "\n=== 1. Form o TRANG HO SO (form moi) ===\n\n";
    inTin('Khách để lại thông tin', [
        'Họ tên' => 'Nguyễn Văn A',
        'Điện thoại' => '0912345678',
        'Email' => null,
        'Đối tượng' => 'Hộ gia đình nghèo, cận nghèo tại khu vực đô thị.',
        'Dự kiến mua' => null,
        'Từ trang' => 'Trang Hồ sơ',
        'Lời nhắn' => null,
    ]);

    echo "\n=== 2. Form chung (de doi chieu) ===\n\n";
    inTin('Khách để lại thông tin', [
        'Họ tên' => 'Trần Thị B',
        'Điện thoại' => '0987654321',
        'Email' => 'b@example.com',
        'Quan tâm' => 'Căn 2PN, tầng trung',
        'Dự kiến mua' => 'Trong 3 tháng',
        'Từ trang' => 'Form chung',
        'Lời nhắn' => 'Cho tôi xin bảng giá.',
    ]);

    echo "\n=== 3. Nut Lien he o tung nhan vien ===\n\n";
    inTin('Khách xin tư vấn qua nhân viên', [
        'Họ tên' => 'Lê Văn C',
        'Điện thoại' => '0900111222',
        'Nhân viên phụ trách' => 'Nguyễn Văn Hùng - 0912 001 001',
        'Dự án' => 'NOXH Túc Duyên',
        'Ghi chú' => 'Tôi muốn hỏi về hồ sơ.',
    ]);

    echo "\n";
} finally {
    // Trả lại nguyên trạng cấu hình.
    foreach ($cu as $k => $giaTri) {
        if ($giaTri === null) {
            DB::table('systems')->where('keyword', $k)->delete();
        } else {
            DB::table('systems')->where('keyword', $k)->update(['content' => $giaTri]);
        }
    }

    echo "(đã trả lại cấu hình Telegram như cũ)\n";
}
