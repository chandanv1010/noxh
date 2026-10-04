<?php
require_once __DIR__ . '/_khoa.php';
/**
 * Dang nhap admin roi kiem tra trang /product/index that su hien du an.
 *
 * Tai khoan dang hoat dong: chi 4504 (publish=2). Cac tai khoan khac publish=1
 * nen khong dang nhap duoc. Script nay:
 *   1. luu lai hash mat khau cu
 *   2. dat tam mat khau moi lay tu bien moi truong NOXH_ADMIN_PASS
 *      (khong viet mat khau thang vao day - xem scratch/_khoa.php)
 *   3. dang nhap that qua HTTP, lay session
 *   4. GET /product/index va dem xem co bao nhieu du an trong bang
 *   5. (tuy chon) khoi phuc hash cu neu truyen tham so restore
 */

$base = 'http://127.0.0.1';
$host = 'noxh.test';
$jar  = sys_get_temp_dir() . '/noxh-admin.txt';
$newPassword = nx_khoa_admin();
$restore = in_array('--restore', $argv, true);

require 'D:/sandbox/noxh/vendor/autoload.php';
$a = require 'D:/sandbox/noxh/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function rq(string $m, string $p, array $post = [], bool $follow = true, bool $json = false): array
{
    global $base, $host, $jar;
    $ch = curl_init($base . $p);
    $headers = ['Host: ' . $host];
    if ($json) {
        $headers[] = 'X-Requested-With: XMLHttpRequest';
        $headers[] = 'Accept: application/json';
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_HEADER         => 1,
    ]);
    if ($m === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hl   = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'h' => substr((string) $raw, 0, $hl), 'b' => substr((string) $raw, $hl)];
}

$backupFile = __DIR__ . '/admin-password-backup.json';
$user = DB::table('users')->where('publish', 2)->whereNull('deleted_at')->orderBy('id')->first();
echo "tai khoan dung de kiem tra: #{$user->id} {$user->email} (publish={$user->publish})\n";

if (! is_file($backupFile)) {
    file_put_contents($backupFile, json_encode([
        'user_id' => $user->id,
        'email'   => $user->email,
        'password_hash' => $user->password,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "da luu hash mat khau cu vao scratch/admin-password-backup.json\n";
} else {
    echo "da co file backup hash tu lan truoc, khong ghi de\n";
}

if ($restore) {
    $bak = json_decode(file_get_contents($backupFile), true);
    DB::table('users')->where('id', $bak['user_id'])->update(['password' => $bak['password_hash']]);
    echo "DA KHOI PHUC mat khau cu cho #{$bak['user_id']} {$bak['email']}\n";
    exit(0);
}

DB::table('users')->where('id', $user->id)->update(['password' => Hash::make($newPassword)]);
echo "da dat mat khau tam (khong in ra man hinh).\n";

@unlink($jar);

/* ---- dang nhap ---- */
$g = rq('GET', '/admin');
preg_match('~name="_token"\s+value="([^"]+)"~', $g['b'], $m);
$token = $m[1] ?? '';
echo "GET /admin -> HTTP {$g['code']}, token=" . ($token ? 'co' : 'KHONG CO') . "\n";

$login = rq('POST', '/login', [
    '_token'   => $token,
    'email'    => $user->email,
    'password' => $newPassword,
]);
echo "POST /login -> HTTP {$login['code']}\n";
if (preg_match('~Location: (.*)~i', $login['h'], $l)) { echo "  chuyen huong: " . trim($l[1]) . "\n"; }

/* ---- kiem tra da dang nhap chua ---- */
$dash = rq('GET', '/dashboard/index');
echo "GET /dashboard/index -> HTTP {$dash['code']}, bytes=" . strlen($dash['b']) . "\n";
$loggedIn = ! str_contains($dash['b'], 'name="password"');
echo "  da dang nhap: " . ($loggedIn ? 'CO' : 'CHUA (van la trang dang nhap)') . "\n";

if (! $loggedIn) {
    echo "\nKhong dang nhap duoc, dung lai. Thong bao loi tren trang:\n";
    if (preg_match_all('~class="[^"]*(alert|error|invalid-feedback)[^"]*"[^>]*>(.*?)<~si', $dash['b'], $e)) {
        foreach (array_slice($e[2], 0, 5) as $t) {
            $t = trim(strip_tags($t));
            if ($t !== '') { echo "  $t\n"; }
        }
    }
    exit(1);
}

/* ---- trang danh sach du an ---- */
echo "\n=== /product/index ===\n";
$pi = rq('GET', '/product/index');
echo "HTTP {$pi['code']}, bytes=" . strlen($pi['b']) . "\n";

/* dem so du an hien ra bang cach tim ten du an trong HTML */
$names = DB::table('product_language')->where('language_id', 1)->pluck('name', 'product_id');
$found = [];
foreach ($names as $id => $name) {
    if (mb_strpos($pi['b'], $name) !== false) { $found[$id] = $name; }
}
echo "ten du an tim thay trong HTML: " . count($found) . "/" . count($names) . "\n";
foreach ($found as $id => $name) { echo "  [$id] $name\n"; }

/* dem so dong <tr> trong bang */
$rows = preg_match_all('~<tr[^>]*>~i', $pi['b']);
$checkbox = preg_match_all('~name="id\[\]"|value="\d+"[^>]*class="[^"]*checkbox|js-check-all-item~i', $pi['b']);
echo "so the <tr>: $rows | checkbox dong: $checkbox\n";

/* co thong bao "khong co du lieu" khong */
foreach (['không có dữ liệu', 'khong co du lieu', 'No data', 'Trống'] as $needle) {
    if (mb_stripos($pi['b'], $needle) !== false) { echo "CO thong bao rong: '$needle'\n"; }
}

/* ---- thu loc theo danh muc qua ajax nhu giao dien lam ---- */
$danhMuc = DB::table('product_catalogues')->orderBy('id')->value('id');
$tenDanhMuc = DB::table('product_catalogue_language')
    ->where('product_catalogue_id', $danhMuc)->where('language_id', 1)->value('name');

echo "\n=== loc theo danh muc #$danhMuc ($tenDanhMuc) ===\n";
$f = rq('GET', '/product/index?product_catalogue_id=' . $danhMuc . '&publish=2');
echo "HTTP {$f['code']}, bytes=" . strlen($f['b']) . "\n";
$cnt = 0;
foreach ($names as $name) {
    if (mb_strpos($f['b'], $name) !== false) {
        $cnt++;
    }
}
echo "  ten du an hien ra: $cnt/" . count($names) . "\n";

echo "\nMat khau tam da dat (khong in ra man hinh). Khoi phuc mat khau cu: php scratch/kiem-tra-admin.php --restore\n";
