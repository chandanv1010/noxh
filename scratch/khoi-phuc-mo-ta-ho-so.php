<?php

/**
 * Khôi phục mô tả bộ hồ sơ về đúng chữ có dấu.
 *
 * Chạy: php scratch/khoi-phuc-mo-ta-ho-so.php
 *
 * Vì sao cần tệp này thay vì gõ thẳng lệnh mysql: PowerShell chuyển chuỗi có dấu
 * qua tham số dòng lệnh bị đổi bảng mã, nên "giấy tờ" thành "gi?y t?". Ghi chuỗi
 * vào tệp PHP (UTF-8) rồi để PHP cập nhật thì không đi qua console nữa.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$moTa = 'Danh sách giấy tờ cần chuẩn bị khi nộp hồ sơ mua nhà ở xã hội.';

DB::table('dossier_sets')->where('publish', 2)->update(['description' => $moTa]);

echo "Đã đặt lại mô tả cho các bộ hồ sơ:\n";

foreach (DB::table('dossier_sets')->where('publish', 2)->get(['id', 'name', 'description']) as $bo) {
    echo sprintf("  #%d  %s\n        %s\n", $bo->id, $bo->name, $bo->description);
}
