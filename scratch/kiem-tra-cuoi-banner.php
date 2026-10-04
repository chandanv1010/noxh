<?php
/** Kiem tra cuoi: slide + anh banner trong CSDL, va anh co ton tai khong. */

$root = 'D:/sandbox/noxh';
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ANH BANNER TRONG CSDL ===\n";
foreach (['hero_image', 'hero_image_mobile'] as $k) {
    $v = DB::table('introduces')->where('keyword', $k)->where('language_id', 1)->value('content');
    $f = $root . '/public' . $v;
    $s = is_file($f) ? getimagesize($f) : null;
    printf("  %-18s %-34s %s\n", $k, $v ?: '(trong)', $s ? $s[0] . 'x' . $s[1] . '  ' . round(filesize($f) / 1024) . ' KB' : 'KHONG THAY FILE');
}

echo "\n=== SLIDE mobile-slide ===\n";
$sl = DB::table('slides')->where('keyword', 'mobile-slide')->first();
if (! $sl) {
    echo "  khong co\n";
} else {
    $item = json_decode($sl->item, true);
    printf("  id=%s  publish=%s  ten=%s\n", $sl->id, $sl->publish, $sl->name);
    printf("  anh: %s\n", $item[1][0]['image'] ?? '(khong co)');
}

echo "\n=== THU TU UU TIEN ANH DIEN THOAI TREN TRANG CHU ===\n";
// Thu tu nay phai khop home/index.blade.php: o trong Cau hinh -> Gioi thieu
// dung TRUOC, slide chi la du phong khi o do de trong.
$slideAnh = DB::table('slides')->where('keyword', 'mobile-slide')->where('publish', 2)->exists()
    ? (json_decode(DB::table('slides')->where('keyword', 'mobile-slide')->value('item'), true)[1][0]['image'] ?? '')
    : '';
$oGioiThieu = DB::table('introduces')->where('keyword', 'hero_image_mobile')->where('language_id', 1)->value('content') ?? '';
echo '  1. o Gioi thieu       : ' . ($oGioiThieu ?: '(khong co)') . "   <-- cho sua chinh\n";
echo '  2. slide mobile-slide : ' . ($slideAnh ?: '(khong co)') . "   (chi dung khi o tren de trong)\n";
echo '  => trang chu dung     : ' . ($oGioiThieu ?: ($slideAnh ?: '(khong co)')) . "\n";
