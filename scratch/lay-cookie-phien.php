<?php
require_once __DIR__ . '/_khoa.php';
/**
 * Lay cookie phien de dua cho script Node dieu khien Chrome.
 * Chay: php scratch/lay-cookie-phien.php [email] [matKhau] [duongDanDangNhap]
 *
 * Mac dinh: tai khoan quan tri. Muon phien nhan vien kinh doanh thi truyen:
 *   php scratch/lay-cookie-phien.php hung.nv@noxh.vn <matKhau> /sale/dang-nhap
 */

$base = 'http://127.0.0.1';
$host = 'noxh.test';
$jar  = sys_get_temp_dir() . '/noxh-cdp.txt';
$email = $argv[1] ?? 'tuannc.dev@gmail.com';
$matKhau = $argv[2] ?? nx_khoa_admin();
$trangDangNhap = $argv[3] ?? '/admin';

function rq(string $m, string $p, array $post = []): array
{
    global $base, $host, $jar;
    $ch = curl_init($base . $p);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_TIMEOUT => 90,
        CURLOPT_HTTPHEADER => ['Host: ' . $host],
        CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_HEADER => 1,
    ]);
    if ($m === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $raw = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $hl = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['code' => $code, 'b' => substr((string) $raw, $hl)];
}

@unlink($jar);
$g = rq('GET', $trangDangNhap);
preg_match('~name="_token"\s+value="([^"]+)"~', $g['b'], $m);
$dangNhap = ($trangDangNhap === '/admin') ? '/login' : $trangDangNhap;
rq('POST', $dangNhap, ['_token' => $m[1] ?? '', 'email' => $email, 'password' => $matKhau]);

$phien = $xsrf = '';
foreach (file($jar) as $line) {
    if (preg_match('~\t(noxhvn_session|XSRF-TOKEN)\t(\S+)\s*$~', rtrim($line), $k)) {
        if ($k[1] === 'noxhvn_session') { $phien = $k[2]; } else { $xsrf = $k[2]; }
    }
}

if (! $phien) {
    echo "khong lay duoc cookie phien\n";
    exit(1);
}

echo "cookie phien lay duoc (" . strlen($phien) . " ky tu)\n";
file_put_contents(sys_get_temp_dir() . '/noxh-cookie.txt', $phien . "\n" . $xsrf);
echo "da luu vao " . sys_get_temp_dir() . "/noxh-cookie.txt\n";
