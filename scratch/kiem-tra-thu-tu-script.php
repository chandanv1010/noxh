<?php
require_once __DIR__ . '/_khoa.php';
/** Kiem tra thu tu cac the <script> trong HTML that cua form. */

$base = 'http://127.0.0.1';
$host = 'noxh.test';
$jar  = sys_get_temp_dir() . '/noxh-thutu.txt';

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
$g = rq('GET', '/admin');
preg_match('~name="_token"\s+value="([^"]+)"~', $g['b'], $m);
rq('POST', '/login', ['_token' => $m[1] ?? '', 'email' => 'tuannc.dev@gmail.com', 'password' => nx_khoa_admin()]);

$r = rq('GET', '/product/76/edit');
$html = $r['b'];

echo "=== THU TU CAC THE SCRIPT TRONG TRANG (HTTP {$r['code']}) ===\n";
preg_match_all('~<script([^>]*)>~i', $html, $the, PREG_OFFSET_CAPTURE);
$so = 0;
foreach ($the[0] as $i => $t) {
    $attrs = $t[0];
    $vt = $t[1];
    if (preg_match('~src="([^"]+)"~', $attrs, $src)) {
        printf("  %2d. [%7d] src=%s\n", ++$so, $vt, $src[1]);
    } else {
        // script noi bo: in dong dau tien co noi dung
        $sau = substr($html, $vt, 900);
        $dong = '';
        foreach (explode("\n", $sau) as $l) {
            $l = trim($l);
            if ($l !== '' && ! str_starts_with($l, '<script')) { $dong = mb_substr($l, 0, 70); break; }
        }
        printf("  %2d. [%7d] (noi bo) %s\n", ++$so, $vt, $dong);
    }
}

echo "\n=== VI TRI cac dau moc quan trong ===\n";
foreach ([
    'jquery-3.1.1.min.js' => 'jQuery',
    'select2.full.min.js' => 'Select2',
    'library.js'          => 'library.js (chay .select2())',
    'location.js'         => 'location.js',
    'nx-tinh'             => 'the select Tinh/Thanh',
    'nx-xa'               => 'the select Phuong/Xa',
    'Select2 KHONG tu goi' => 'script cascade cua toi',
] as $moc => $ten) {
    $vt = strpos($html, $moc);
    printf("  %-32s %s\n", $ten, $vt === false ? 'KHONG THAY' : "vi tri $vt");
}
