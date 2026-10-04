<?php
require_once __DIR__ . '/_khoa.php';
/**
 * Kiem tra vong doi day du: tao du an -> luu tinh/phuong/xa -> doc lai -> xoa.
 * Dung mot du an TAM roi xoa, khong dung vao 6 du an that.
 *
 * Chay: php scratch/kiem-tra-luu-du-an.php
 */

$base = 'http://127.0.0.1';
$host = 'noxh.test';
$jar  = sys_get_temp_dir() . '/noxh-luu.txt';

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
    return ['code' => $code, 'h' => substr((string) $raw, 0, $hl), 'b' => substr((string) $raw, $hl)];
}

function token(string $html): string
{
    preg_match('~name="_token"\s+value="([^"]+)"~', $html, $m);
    return $m[1] ?? '';
}

/* ---- dang nhap ---- */
@unlink($jar);
rq('POST', '/login', [
    '_token' => token(rq('GET', '/admin')['b']),
    'email' => 'tuannc.dev@gmail.com',
    'password' => nx_khoa_admin(),
]);
$daVao = ! str_contains(rq('GET', '/dashboard/index')['b'], 'name="password"');
echo "dang nhap: " . ($daVao ? 'OK' : 'THAT BAI') . "\n";
if (! $daVao) {
    exit(1);
}

/* ---- chon tinh/phuong/xa de thu: Bắc Ninh (24) + phuong dau tien cua no ---- */
$maTinh = '24';
$tenTinh = DB::table('vn_provinces')->where('code', $maTinh)->value('name');
$xa = DB::table('vn_wards')->where('province_code', $maTinh)->orderBy('order')->first();
echo "\nthu voi: tinh $maTinh ($tenTinh) + xa {$xa->code} ({$xa->name})\n";

$canonical = 'du-an-kiem-tra-tam-' . date('His');
$danhMuc = DB::table('product_catalogues')->orderBy('id')->value('id');

/* ---- 1. POST tao du an ---- */
$form = rq('GET', '/product/create');
echo "\n=== 1. TAO DU AN QUA FORM ===\n";
$post = [
    '_token' => token($form['b']),
    'name' => 'DU AN KIEM TRA TAM',
    'canonical' => $canonical,
    'product_catalogue_id' => $danhMuc,
    'province_code' => $maTinh,
    'ward_code' => $xa->code,
    'address' => 'Dia chi kiem tra tam',
    'latitude' => '21.186100',
    'longitude' => '106.076300',
    'status' => 'building',
    'price_from' => '17.5',
    'price_to' => '21.0',
    'area_from' => '30',
    'area_to' => '65',
    'total_units' => '999',
    'total_land_area' => '3.21',
    'scale_description' => '02 khoi chung cu kiem tra',
    'apartment_types' => '1PN, 2PN',
    'ownership_type' => 'So huu 50 nam',
    'timeline_label' => 'Quy II/2027',
    'publish' => '2',
    'follow' => '2',
];
$tao = rq('POST', '/product/store', $post);
echo "POST /product/store -> HTTP {$tao['code']}\n";
if (preg_match('~Location: (.*)~i', $tao['h'], $l)) {
    echo "  chuyen huong: " . trim($l[1]) . "\n";
}

$row = DB::table('product_language')->where('canonical', $canonical)->first();
if (! $row) {
    echo "  KHONG tao duoc du an. Thong bao loi tren trang:\n";
    if (preg_match_all('~class="[^"]*(invalid-feedback|alert-danger)[^"]*"[^>]*>(.*?)<~si', $tao['b'], $e)) {
        foreach (array_slice($e[2], 0, 6) as $t) {
            $t = trim(strip_tags($t));
            if ($t !== '') { echo "    $t\n"; }
        }
    }
    exit(1);
}

$moi = DB::table('products')->where('id', $row->product_id)->first();
$id = $moi->id;
printf("  da tao: id=%d ten='%s'\n", $id, $row->name);
printf("  CSDL luu: province_code='%s'  ward_code='%s'  lat=%s lng=%s\n",
    $moi->province_code, $moi->ward_code, $moi->latitude, $moi->longitude);
printf("  khop voi form gui len? tinh=%s xa=%s\n",
    $moi->province_code === $maTinh ? 'DUNG' : 'SAI',
    (string) $moi->ward_code === (string) $xa->code ? 'DUNG' : 'SAI');
printf("  cac cot khac: status=%s gia=%s-%s dien tich=%s-%s can=%s ha=%s\n",
    $moi->status, $moi->price_from, $moi->price_to, $moi->area_from, $moi->area_to,
    $moi->total_units, $moi->total_land_area);

/* ---- 2. Doc lai form sua ---- */
echo "\n=== 2. MO LAI FORM SUA ===\n";
$edit = rq('GET', "/product/{$id}/edit");
echo "HTTP {$edit['code']}, bytes=" . strlen($edit['b']) . "\n";
$checks = [
    'latitude' => '21.186100',
    'longitude' => '106.076300',
    'address' => 'Dia chi kiem tra tam',
    'scale_description' => '02 khoi chung co kiem tra',
    'timeline_label' => 'Quy II/2027',
];
$doc = 0;
foreach ($checks as $cot => $mongDoi) {
    if (preg_match('~name="' . $cot . '"[^>]*value="([^"]*)"~', $edit['b'], $v)) {
        $khop = trim($v[1]) === $mongDoi;
        $khop && $doc++;
        printf("  %-20s = '%s' %s\n", $cot, $v[1], $khop ? 'OK' : "MONG DOI '$mongDoi'");
    }
}
if (preg_match('~<option value="' . $maTinh . '" selected~', $edit['b'])) {
    echo "  tinh dang chon    = $maTinh OK\n";
    $doc++;
}
if (preg_match('~var dangChon = "([^"]*)"~', $edit['b'], $w)) {
    $khop = $w[1] === (string) $xa->code;
    $khop && $doc++;
    printf("  phuong/xa trong JS = '%s' %s\n", $w[1], $khop ? 'OK' : "MONG DOI '{$xa->code}'");
}

/* ---- 3. Trang chi tiet ngoai web ---- */
echo "\n=== 3. TRANG CHI TIET NGOAI WEB ===\n";
$show = rq('GET', '/du-an/' . $canonical);
echo "GET /du-an/$canonical -> HTTP {$show['code']}\n";
foreach ([
    'ten du an' => 'DU AN KIEM TRA TAM',
    'quy mo (3.21 ha)' => '3,21',
    'so can (999)' => '999',
    'dien tich (30-65)' => '30',
    'gia (17,5-21)' => '17,5',
    'ten phuong/xa' => nx_ten_dia_gioi_ngan($xa->name),
] as $nhan => $needle) {
    printf("  %-22s %s\n", $nhan, str_contains($show['b'], $needle) ? 'HIEN' : 'KHONG thay');
}

/* ---- 4. Don sach ---- */
echo "\n=== 4. XOA DU AN TAM ===\n";
DB::table('product_language')->where('product_id', $id)->delete();
DB::table('product_catalogue_product')->where('product_id', $id)->delete();
DB::table('routers')->where('module_id', $id)->where('controllers', 'like', '%Product%')->delete();
DB::table('products')->where('id', $id)->delete();
echo "  da xoa du an $id, con lai " . DB::table('products')->count() . " du an that\n";
echo "\nket qua: $doc/7 o doc lai dung\n";
