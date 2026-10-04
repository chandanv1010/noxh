<?php
require_once __DIR__ . '/_khoa.php';
/**
 * Do chieu cao cac o tren FORM DU AN CUA NHAN VIEN KINH DOANH.
 *
 * Tai khoan nhan vien la DEMO, khong ai biet mat khau, nen script nay dat tam roi
 * KHOI PHUC nguyen hash cu trong khoi finally.
 */

$root = 'D:/sandbox/noxh';
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$matKhauTam = nx_khoa_sale();
$nv = DB::table('users')
    ->join('user_catalogues', 'users.user_catalogue_id', '=', 'user_catalogues.id')
    ->where('user_catalogues.is_sale', 1)->where('users.publish', 2)->whereNull('users.deleted_at')
    ->select('users.id', 'users.email', 'users.password')->first();

$hashCu = $nv->password;
DB::table('users')->where('id', $nv->id)->update(['password' => Hash::make($matKhauTam)]);

try {
    $duAn = DB::table('product_user')->where('user_id', $nv->id)->value('product_id') ?: 74;
    $url = "/sale/du-an/{$duAn}/sua";

    /* Lay cookie phien cua nhan vien */
    $php = 'C:\laragon\bin\php\php-8.4.2-nts-Win32-vs17-x64\php.exe';
    $lenh = sprintf(
        '"%s" %s %s %s %s',
        $php,
        escapeshellarg($root . '/scratch/lay-cookie-phien.php'),
        escapeshellarg($nv->email),
        escapeshellarg($matKhauTam),
        escapeshellarg('/sale/dang-nhap')
    );
    exec($lenh, $ra, $ma);
    echo "lay cookie: " . implode(' | ', $ra) . "\n";

    $cookie = trim((string) @file_get_contents(sys_get_temp_dir() . '/noxh-cookie.txt'));
    $cookie = explode("\n", $cookie)[0];

    echo "do chieu cao tren: $url\n";
    $lenh2 = sprintf(
        '"C:\Program Files\nodejs\node.exe" %s %s %s',
        escapeshellarg($root . '/scratch/do-chieu-cao-o.cjs'),
        escapeshellarg($cookie),
        escapeshellarg($url)
    );
    passthru($lenh2);
} finally {
    DB::table('users')->where('id', $nv->id)->update(['password' => $hashCu]);
    echo "\n=== da khoi phuc mat khau #{$nv->id} {$nv->email}: "
        . (DB::table('users')->where('id', $nv->id)->value('password') === $hashCu ? 'hash y nguyen' : 'LOI - hash da doi')
        . " ===\n";
}
