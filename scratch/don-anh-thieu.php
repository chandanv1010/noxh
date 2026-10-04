<?php
/**
 * Don cac duong dan anh trong CSDL tro toi tep khong con ton tai.
 *
 * Chay: php scratch/don-anh-thieu.php            (chi bao cao)
 *       php scratch/don-anh-thieu.php --sua      (sua that)
 *
 * Truong hop cu the: thanh trai trang quan tri
 * (resources/views/backend/dashboard/component/sidebar.blade.php:19) hien anh
 * cua tai khoan dang nhap:
 *
 *     src="{{ $toi?->image ?: asset('uploads/noxh/avatar-mac-dinh.png') }}"
 *
 * Tai khoan trong ban dump tro toi /userfiles/image/1750518865_...png - tep nay
 * khong duoc kem theo ban clone, nen MOI trang quan tri deu bao 404 mot anh.
 * Dat `image` ve NULL thi giao dien tu dung anh mac dinh, dung y do cua ma nguon.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$sua = in_array('--sua', $argv, true);

/** Doi duong dan trong CSDL thanh duong dan tep that tren dia. */
function thanh_duong_dan_tep(?string $duong): ?string
{
    if ($duong === null || trim($duong) === '') return null;
    $duong = str_replace('/public/', '/', trim($duong));
    return public_path(ltrim($duong, '/'));
}

echo "=== TAI KHOAN co anh dai dien tro toi tep khong ton tai ===\n";
$soTaiKhoan = 0;
foreach (DB::table('users')->select('id', 'name', 'email', 'image')->get() as $u) {
    $tep = thanh_duong_dan_tep($u->image);
    if ($tep === null || is_file($tep)) continue;

    $soTaiKhoan++;
    printf("  id=%-6s %-22s %s\n", $u->id, mb_substr((string) $u->name, 0, 22), $u->image);
    if ($sua) {
        DB::table('users')->where('id', $u->id)->update(['image' => null]);
        echo "         -> da dat image = NULL (giao dien se dung anh mac dinh)\n";
    }
}
if ($soTaiKhoan === 0) echo "  (khong co)\n";

echo "\n=== NGON NGU co anh co tro toi tep khong ton tai ===\n";
$soNgonNgu = 0;
foreach (DB::table('languages')->get() as $l) {
    $tep = thanh_duong_dan_tep($l->image);
    $co = $tep === null ? false : is_file($tep);
    $nhan = $co ? 'CO' : ($l->publish == 2 ? 'THIEU (dang bat)' : 'thieu (ban nhap)');
    printf("  id=%-3s %-14s %-58s %s\n", $l->id, $l->name, (string) $l->image, $nhan);
    if (!$co && $l->publish == 2) $soNgonNgu++;
}
if ($soNgonNgu) {
    echo "  -> chay `python tools/lam-anh-co.py` de ve lai cac anh co nay.\n";
}

echo "\n";
echo $sua ? "DA SUA.\n" : "Chay lai voi --sua de sua that.\n";
