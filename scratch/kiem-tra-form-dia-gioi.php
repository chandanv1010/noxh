<?php
require_once __DIR__ . '/_khoa.php';
/**
 * Kiem tra form tao/sua du an sau khi sua dia gioi hanh chinh.
 * Chay: php scratch/kiem-tra-form-dia-gioi.php
 */

$base = 'http://127.0.0.1';
$host = 'noxh.test';
$jar  = sys_get_temp_dir() . '/noxh-form.txt';
$password = nx_khoa_admin();

$root = 'D:/sandbox/noxh';
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

function rq(string $m, string $p, array $post = [], bool $follow = true): array
{
    global $base, $host, $jar;
    $ch = curl_init($base . $p);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_HTTPHEADER     => ['Host: ' . $host],
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

/* ---- dang nhap ---- */
@unlink($jar);
$g = rq('GET', '/admin');
preg_match('~name="_token"\s+value="([^"]+)"~', $g['b'], $m);
$login = rq('POST', '/login', [
    '_token' => $m[1] ?? '',
    'email' => 'tuannc.dev@gmail.com',
    'password' => $password,
]);
$dash = rq('GET', '/dashboard/index');
$daVao = ! str_contains($dash['b'], 'name="password"');
echo "dang nhap: " . ($daVao ? 'OK' : 'THAT BAI') . "\n";
if (! $daVao) {
    exit(1);
}

/** Doc khoi o chon tinh trong HTML va tra ve danh sach ma tinh. */
function docTinh(string $html, string $id): array
{
    if (! preg_match('~<select[^>]*id="' . preg_quote($id, '~') . '".*?</select>~s', $html, $k)) {
        return [];
    }
    preg_match_all('~<option value="([^"]*)"~', $k[0], $o);

    return array_values(array_filter($o[1], fn ($v) => $v !== ''));
}

/* ---- 1. FORM TAO MOI ---- */
echo "\n=== 1. FORM TAO MOI /product/create ===\n";
$r = rq('GET', '/product/create');
echo "HTTP {$r['code']}, bytes=" . strlen($r['b']) . "\n";

$conQH = preg_match('~Quận/Huyện~u', $r['b']);
echo "  con o 'Quận/Huyện'? " . ($conQH ? 'CON' : 'KHONG (da bo)') . "\n";
$conLocation = preg_match('~class="[^"]*\blocation\b[^"]*"~', $r['b']);
echo "  con class 'location' (hook cua location.js)? " . ($conLocation ? 'CON' : 'KHONG') . "\n";

$coNxTinh = str_contains($r['b'], 'id="nx-tinh"');
$coNxXa = str_contains($r['b'], 'id="nx-xa"');
$coGoc = str_contains($r['b'], '/dia-gioi/phuong-xa');
echo "  co o id=nx-tinh / id=nx-xa / endpoint moi? "
    . ($coNxTinh ? 'co' : 'KHONG') . ' / ' . ($coNxXa ? 'co' : 'KHONG') . ' / ' . ($coGoc ? 'co' : 'KHONG') . "\n";

$tinhForm = docTinh($r['b'], 'nx-tinh');
$that = DB::table('vn_provinces')->pluck('code')->map(fn ($c) => (string) $c)->all();
$cu = DB::table('provinces')->pluck('code')->map(fn ($c) => (string) $c)->all();

echo "  so tinh trong o chon: " . count($tinhForm) . "\n";
echo "  khop voi vn_provinces (34 tinh)? " . (count(array_diff($tinhForm, $that)) === 0 && count($tinhForm) === count($that) ? 'DUNG' : 'SAI') . "\n";
$chiCoOBangCu = array_diff($tinhForm, $that);
echo "  tinh chi co trong bang cu (khong duoc xuat hien): "
    . (count($chiCoOBangCu) ? implode(', ', array_slice($chiCoOBangCu, 0, 5)) : 'khong co') . "\n";

/* Ten cac tinh da sap nhap khong duoc con */
foreach (['Thái Bình', 'Bắc Giang', 'Quảng Nam', 'Thừa Thiên Huế'] as $tenCu) {
    printf("    '%s' con trong o chon? %s\n", $tenCu, str_contains($r['b'], '>' . $tenCu . '<') ? 'CON' : 'khong');
}

/* ---- 2. FORM SUA ---- */
echo "\n=== 2. FORM SUA /product/76/edit ===\n";
$r2 = rq('GET', '/product/76/edit');
echo "HTTP {$r2['code']}, bytes=" . strlen($r2['b']) . "\n";
$tinhSua = docTinh($r2['b'], 'nx-tinh');
echo "  so tinh: " . count($tinhSua) . "\n";
$sel = '';
if (preg_match('~<select[^>]*id="nx-tinh".*?</select>~s', $r2['b'], $k)) {
    if (preg_match('~<option value="([^"]+)"\s+selected~', $k[0], $s)) {
        $sel = $s[1];
    } elseif (preg_match('~<option value="([^"]+)"[^>]*selected~', $k[0], $s)) {
        $sel = $s[1];
    }
}
$p76 = DB::table('products')->where('id', 76)->first();
echo "  tinh dang duoc chon: '{$sel}' (du an 76 co province_code='" . $p76->province_code . "')\n";
echo "  khop? " . ($sel === (string) $p76->province_code ? 'DUNG' : 'SAI') . "\n";

$wardJs = '';
if (preg_match('~var\s+dangChon\s*=\s*(.+?);~s', $r2['b'], $w)) {
    $wardJs = trim($w[1]);
}
echo "  ma phuong/xa dua vao JS: {$wardJs} (du an 76 co ward_code='" . ($p76->ward_code ?? '') . "')\n";
echo "  khop? " . (str_contains($wardJs, (string) $p76->ward_code) ? 'DUNG' : 'SAI') . "\n";

/* ---- 3. ENDPOINT PHUONG/XA ---- */
echo "\n=== 3. ENDPOINT /dia-gioi/phuong-xa/{maTinh} ===\n";
foreach (['01', '24', '19'] as $ma) {
    $w = rq('GET', '/dia-gioi/phuong-xa/' . $ma, [], false);
    $ds = json_decode($w['b'], true) ?: [];
    $ten = DB::table('vn_provinces')->where('code', $ma)->value('name');
    $daiMa = $ds && strlen((string) $ds[0]['code']) === 5;
    printf("  %s (%s): HTTP %d, %d phuong/xa, ma 5 chu so? %s\n", $ma, $ten, $w['code'], count($ds), $daiMa ? 'dung' : 'SAI');
    if ($ds) {
        printf("      vi du: %s - %s\n", $ds[0]['code'], $ds[0]['name']);
    }
}

/* ---- 4. BO LOC TINH O TRANG /du-an ---- */
echo "\n=== 4. BO LOC TINH O /du-an ===\n";
$r4 = rq('GET', '/du-an');
echo "HTTP {$r4['code']}, bytes=" . strlen($r4['b']) . "\n";
$coTinhCu = false;
foreach (['Thái Bình', 'Bắc Giang', 'Quảng Nam'] as $tenCu) {
    if (str_contains($r4['b'], '>' . $tenCu . '<')) {
        $coTinhCu = true;
        echo "  CON tinh da sap nhap: $tenCu\n";
    }
}
echo "  tinh da sap nhap trong bo loc: " . ($coTinhCu ? 'CON' : 'khong con') . "\n";

/* So option tinh trong bo loc */
if (preg_match('~<select name="province_code".*?</select>~s', $r4['b'], $bf)) {
    preg_match_all('~<option value="([^"]*)"~', $bf[0], $o);
    $dsTinh = array_values(array_filter($o[1], fn ($v) => $v !== ''));
    echo "  so tinh trong bo loc: " . count($dsTinh) . "\n";
    $sai = array_diff($dsTinh, $that);
    echo "  toan bo deu thuoc vn_provinces? " . (count($sai) === 0 ? 'DUNG' : 'SAI: ' . implode(',', $sai)) . "\n";
}

/* ---- 5. TRANG CHU + BAN DO khong bi anh huong ---- */
echo "\n=== 5. CAC TRANG KHAC VAN CHAY ===\n";
foreach (['/', '/du-an/ban-do', '/du-an/tinh-thanh', '/du-an/noxh-evergreen-bac-giang', '/kiem-tra-dieu-kien/cau-hoi'] as $u) {
    $x = rq('GET', $u);
    printf("  %-40s HTTP %d (%d bytes)\n", $u, $x['code'], strlen($x['b']));
}
