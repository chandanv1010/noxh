<?php
/**
 * Kiem tra chuc nang that: gui form lead + tra cuu ket qua kiem tra dieu kien.
 * Chay: php scratch/test-chuc-nang.php
 */

$base = 'http://127.0.0.1';
$host = 'noxh.test';

function req(string $method, string $path, array $post = [], ?string $cookieJar = null, array $headers = []): array
{
    global $base, $host;
    $ch = curl_init($base . $path);
    $h = array_merge(['Host: ' . $host], $headers);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTPHEADER     => $h,
        CURLOPT_USERAGENT      => 'NOXH-test/1.0',
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    if ($cookieJar) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    return ['code' => $code, 'body' => (string) $body, 'type' => (string) $type, 'url' => $base . $path];
}

$jar = sys_get_temp_dir() . '/noxh-cookies.txt';
@unlink($jar);

/* ---------- 1. form kiem tra dieu kien: lay token roi gui buoc 1 ---------- */
echo "=== 1. WIZARD KIEM TRA DIEU KIEN ===\n";
$r = req('GET', '/kiem-tra-dieu-kien/cau-hoi', [], $jar);
preg_match('~name="_token"\s+value="([^"]+)"~', $r['body'], $m);
$token = $m[1] ?? '';
echo "GET  /kiem-tra-dieu-kien/cau-hoi  -> HTTP {$r['code']}, token=" . ($token ? substr($token, 0, 12) . '...' : 'KHONG THAY') . "\n";

if ($token) {
    $r2 = req('POST', '/kiem-tra-dieu-kien/cau-hoi/1', ['_token' => $token], $jar);
    echo "POST /kiem-tra-dieu-kien/cau-hoi/1 -> HTTP {$r2['code']}, bytes=" . strlen($r2['body']) . "\n";
}

/* ---------- 2. lookup ma ket qua ---------- */
echo "\n=== 2. TRA CUU MA KET QUA ===\n";
require 'D:/sandbox/noxh/vendor/autoload.php';
$a = require 'D:/sandbox/noxh/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$codes = Illuminate\Support\Facades\DB::table('eligibility_checks')->limit(5)->pluck('code')->all();
echo "ma co trong CSDL: " . (implode(', ', $codes) ?: '(khong co)') . "\n";

foreach (array_slice($codes, 0, 2) as $code) {
    $r3 = req('GET', '/kiem-tra-dieu-kien/ket-qua/' . $code);
    $t = '';
    if (preg_match('~<title>(.*?)</title>~si', $r3['body'], $mm)) {
        $t = trim(html_entity_decode(strip_tags($mm[1])));
    }
    echo "GET  /kiem-tra-dieu-kien/ket-qua/$code -> HTTP {$r3['code']} | $t\n";
}

/* ---------- 3. form lien he / tu van (lead) ---------- */
echo "\n=== 3. FORM LEAD (tu van) ===\n";
$r4 = req('GET', '/cong-hoa/tu-van', [], $jar);
preg_match('~name="_token"\s+value="([^"]+)"~', $r4['body'], $m4);
$token4 = $m4[1] ?? '';
echo "GET  /cong-hoa/tu-van -> HTTP {$r4['code']}, token=" . ($token4 ? 'co' : 'khong') . "\n";

$before = Illuminate\Support\Facades\DB::table('contacts')->count();
$r5 = req('POST', '/lien-he-tu-van', [
    '_token'  => $token4,
    'name'    => 'Kiem tra tu dong',
    'phone'   => '0912345678',
    'email'   => 'test@example.com',
    'message' => 'Du lieu do he thong kiem tra tao ra, co the xoa.',
], $jar);
$after = Illuminate\Support\Facades\DB::table('contacts')->count();
echo "POST /lien-he-tu-van -> HTTP {$r5['code']} | contacts: $before -> $after\n";
$row = Illuminate\Support\Facades\DB::table('contacts')->orderByDesc('id')->first();
if ($row) {
    echo "ban ghi moi: id={$row->id} name='" . ($row->name ?? '') . "' phone='" . ($row->phone ?? '') . "'\n";
}

/* ---------- 4. tim kiem ---------- */
echo "\n=== 4. TIM KIEM ===\n";
$r6 = req('GET', '/tim-kiem?q=noxh');
$n = preg_match_all('~class="[^"]*(project|news)-item~', $r6['body']);
echo "GET  /tim-kiem?q=noxh -> HTTP {$r6['code']}, ket qua hien thi ~$n\n";

/* ---------- 5. dang nhap quan tri ---------- */
echo "\n=== 5. DANG NHAP QUAN TRI ===\n";
$admin = Illuminate\Support\Facades\DB::table('users')->orderBy('id')->first();
echo "tai khoan dau tien: " . ($admin->email ?? '(khong ro)') . " | nhom=" . ($admin->user_catalogue_id ?? '?') . "\n";
$r7 = req('GET', '/admin', [], $jar);
preg_match('~name="_token"\s+value="([^"]+)"~', $r7['body'], $m7);
echo "GET  /admin -> HTTP {$r7['code']}, form dang nhap=" . (isset($m7[1]) ? 'co' : 'khong') . "\n";
