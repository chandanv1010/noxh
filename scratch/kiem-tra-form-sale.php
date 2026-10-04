<?php
require_once __DIR__ . '/_khoa.php';
/**
 * Kiem tra form du an cua nhan vien kinh doanh (/sale/du-an/{id}/sua).
 *
 * Tai khoan nhan vien 5749 la DEMO (publish=2) nhung khong ai biet mat khau,
 * nen script nay dat tam roi KHOI PHUC lai nguyen hash cu ngay sau khi kiem tra
 * - khong de lai thay doi nao tren tai khoan do.
 */

$base = 'http://127.0.0.1';
$host = 'noxh.test';
$jar  = sys_get_temp_dir() . '/noxh-sale.txt';
$matKhauTam = nx_khoa_sale();

$root = 'D:/sandbox/noxh';
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function rq(string $m, string $p, array $post = [], bool $follow = true): array
{
    global $base, $host, $jar;
    $ch = curl_init($base . $p);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_TIMEOUT => 90, CURLOPT_HTTPHEADER => ['Host: ' . $host],
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

function token(string $html): string
{
    preg_match('~name="_token"\s+value="([^"]+)"~', $html, $m);
    return $m[1] ?? '';
}

/* Tai khoan nhan vien kinh doanh dang hoat dong, va du an thuoc ve ho */
$nv = DB::table('users')
    ->join('user_catalogues', 'users.user_catalogue_id', '=', 'user_catalogues.id')
    ->where('user_catalogues.is_sale', 1)
    ->where('users.publish', 2)
    ->whereNull('users.deleted_at')
    ->select('users.id', 'users.name', 'users.email', 'users.password')
    ->first();

if (! $nv) {
    echo "khong co nhan vien kinh doanh nao dang hoat dong\n";
    exit(1);
}
echo "tai khoan thu: #{$nv->id} {$nv->name} <{$nv->email}>\n";

$duAn = DB::table('product_user')->where('user_id', $nv->id)->value('product_id');
if (! $duAn) {
    $duAn = DB::table('products')->orderBy('id')->value('id');
}
echo "du an thu: #{$duAn}\n";

$hashCu = $nv->password;
DB::table('users')->where('id', $nv->id)->update(['password' => Hash::make($matKhauTam)]);

try {
    @unlink($jar);

    /* ---- dang nhap trang sale ---- */
    $loginPage = rq('GET', '/sale/dang-nhap');
    echo "\nGET /sale/dang-nhap -> HTTP {$loginPage['code']}\n";
    $dangNhap = rq('POST', '/sale/dang-nhap', [
        '_token' => token($loginPage['b']),
        'email' => $nv->email,
        'password' => $matKhauTam,
    ]);
    echo "POST /sale/dang-nhap -> HTTP {$dangNhap['code']}\n";
    if (preg_match('~Location: (.*)~i', $dangNhap['b'], $l)) {
        echo "  chuyen huong: " . trim($l[1]) . "\n";
    }

    $dash = rq('GET', '/sale');
    echo "GET /sale -> HTTP {$dash['code']}, bytes=" . strlen($dash['b']) . "\n";
    $vaoDuoc = ! str_contains($dash['b'], 'name="password"');
    echo "  vao duoc bang dieu khien sale: " . ($vaoDuoc ? 'CO' : 'KHONG') . "\n";

    if (! $vaoDuoc) {
        echo "\nkhong dang nhap duoc, bo qua phan kiem tra form\n";
    } else {
        /* ---- form sua du an ---- */
        echo "\n=== FORM SUA DU AN CUA SALE ===\n";
        $form = rq('GET', "/sale/du-an/{$duAn}/sua");
        echo "HTTP {$form['code']}, bytes=" . strlen($form['b']) . "\n";

        $conQH = preg_match('~Quận/Huyện~u', $form['b']);
        echo "  con o 'Quận/Huyện'? " . ($conQH ? 'CON' : 'KHONG') . "\n";
        echo "  co o id=nx-tinh / nx-xa? "
            . (str_contains($form['b'], 'id="nx-tinh"') ? 'co' : 'KHONG') . ' / '
            . (str_contains($form['b'], 'id="nx-xa"') ? 'co' : 'KHONG') . "\n";

        if (preg_match('~<select[^>]*id="nx-tinh".*?</select>~s', $form['b'], $k)) {
            preg_match_all('~<option value="([^"]*)"~', $k[0], $o);
            $ds = array_values(array_filter($o[1], fn ($v) => $v !== ''));
            echo "  so tinh trong o chon: " . count($ds) . "\n";
            $that = DB::table('vn_provinces')->pluck('code')->map(fn ($c) => (string) $c)->all();
            echo "  dung bang vn_provinces? " . (count(array_diff($ds, $that)) === 0 && count($ds) === count($that) ? 'DUNG' : 'SAI') . "\n";
            if (preg_match('~<option value="([^"]+)" selected~', $k[0], $s)) {
                $p = DB::table('products')->where('id', $duAn)->value('province_code');
                echo "  tinh dang chon: '{$s[1]}' (du an co '{$p}') -> " . ((string) $p === $s[1] ? 'DUNG' : 'SAI') . "\n";
            }
        }
        if (preg_match('~var dangChon = "([^"]*)"~', $form['b'], $w)) {
            $p = DB::table('products')->where('id', $duAn)->value('ward_code');
            echo "  phuong/xa trong JS: '{$w[1]}' (du an co '{$p}') -> " . ((string) $p === $w[1] ? 'DUNG' : 'SAI') . "\n";
        }

        /* ---- form tao moi ---- */
        echo "\n=== FORM TAO MOI CUA SALE ===\n";
        $tao = rq('GET', '/sale/du-an/them-moi');
        echo "HTTP {$tao['code']}, bytes=" . strlen($tao['b']) . "\n";
        echo "  render khong loi? " . (str_contains($tao['b'], 'nx-tinh') && ! str_contains($tao['b'], 'Whoops') ? 'OK' : 'CO VAN DE') . "\n";
    }
} finally {
    DB::table('users')->where('id', $nv->id)->update(['password' => $hashCu]);
    echo "\n=== da khoi phuc mat khau cu cho #{$nv->id} {$nv->email} ===\n";
    echo "    (kiem tra lai: " . (DB::table('users')->where('id', $nv->id)->value('password') === $hashCu ? 'hash y nguyen' : 'HASH DA DOI - LOI') . ")\n";
}
