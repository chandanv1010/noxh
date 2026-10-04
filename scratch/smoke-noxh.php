<?php
/**
 * Kiem tra toan bo website NOXH qua Apache/Laragon.
 * Chay:  php smoke-noxh.php
 */

$app  = 'D:/sandbox/noxh';
$base = 'http://127.0.0.1';
$host = 'noxh.test';

require $app . '/vendor/autoload.php';
$a = require $app . '/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/* ---------- 1. danh sach route frontend ---------- */
$skipTop = ['admin', 'ajax', 'api', 'sanctum', 'storage', 'build', 'sale', '_ignition', 'livewire', 'thumb', 'language'];
$urls = [];
foreach (Route::getRoutes() as $route) {
    if (! in_array('GET', $route->methods(), true)) {
        continue;
    }
    $uri = $route->uri();
    if (str_contains($uri, '{') && ! in_array($uri, ['kiem-tra-dieu-kien/cau-hoi/{buoc?}'], true)) {
        continue;
    }
    $top = explode('/', $uri)[0];
    if (in_array($top, $skipTop, true)) {
        continue;
    }
    $path = '/' . ltrim(str_replace('{buoc?}', '', $uri), '/');
    $path = rtrim($path, '/');
    $path = $path === '' ? '/' : $path;
    $urls[$path] = $route->getName() ?? '';
}

/* ---------- 2. trang CMS that trong CSDL ---------- */
$pages = DB::table('routers')->pluck('canonical')->all();
foreach ($pages as $c) {
    $p = '/' . ltrim($c, '/');
    $urls[$p] = 'cms-page';
}

/* ---------- 3. du an + tin tuc that ---------- */
foreach (DB::table('products')->whereNull('deleted_at')->limit(10)->pluck('id') as $id) {
    $row = DB::table('product_language')->where('product_id', $id)->first();
    if ($row && $row->canonical) {
        $urls['/du-an/' . $row->canonical] = 'du-an:' . mb_substr($row->name ?? '', 0, 30);
    }
}
foreach (DB::table('posts')->whereNull('deleted_at')->limit(10)->pluck('id') as $id) {
    $row = DB::table('post_language')->where('post_id', $id)->first();
    if ($row && $row->canonical) {
        $urls['/tin-tuc/' . $row->canonical] = 'tin:' . mb_substr($row->name ?? '', 0, 30);
    }
}
/* mot tinh co du an */
$prov = DB::table('vn_provinces')->first();
if ($prov) {
    $urls['/du-an/tinh-thanh/' . $prov->code] = 'tinh:' . $prov->name;
}

ksort($urls);

echo "KIEM TRA ", count($urls), " URL qua http://$host (Apache/Laragon)", PHP_EOL;
echo str_repeat('=', 104), PHP_EOL;

$ok = 0;
$bad = [];
$slow = [];

foreach ($urls as $path => $label) {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 90,
        CURLOPT_HTTPHEADER     => ['Host: ' . $host],
        CURLOPT_USERAGENT      => 'NOXH-smoke/2.0',
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ms   = (int) round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000);
    curl_close($ch);

    $body = (string) $body;
    $mark = ($code >= 200 && $code < 400) ? 'OK ' : 'BAD';
    if ($mark === 'OK ') { $ok++; } else { $bad[$path] = $code; }
    if ($ms > 2000) { $slow[$path] = $ms; }

    $title = '';
    if (preg_match('~<title>(.*?)</title>~si', $body, $m)) {
        $title = mb_substr(trim(html_entity_decode(strip_tags($m[1]))), 0, 46);
    }

    printf("%s %-4s %-44s %7dB %6dms  %s\n", $mark, $code, $path, strlen($body), $ms, $title);
}

echo str_repeat('=', 104), PHP_EOL;
printf("TONG=%d  OK=%d  LOI=%d\n", count($urls), $ok, count($bad));
foreach ($bad as $p => $c) {
    echo "  LOI  $p -> HTTP $c\n";
}
if ($slow) {
    echo "Cham (>2s): ";
    foreach ($slow as $p => $ms) { echo "$p={$ms}ms  "; }
    echo PHP_EOL;
}
