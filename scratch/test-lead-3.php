<?php
/** Kiem tra 2 duong thu lead that: de-lai-thong-tin (store) va lien-he-tu-van (advisor). */

$base = 'http://127.0.0.1';
$host = 'noxh.test';
$jar  = sys_get_temp_dir() . '/noxh-lead.txt';
@unlink($jar);

function rq(string $m, string $p, array $post = [], bool $follow = false): array
{
    global $base, $host, $jar;
    $ch = curl_init($base . $p);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FOLLOWLOCATION => $follow ? 1 : 0,
        CURLOPT_TIMEOUT        => 60,
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

require 'D:/sandbox/noxh/vendor/autoload.php';
$a = require 'D:/sandbox/noxh/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$token = function (string $page): string {
    $g = rq('GET', $page);
    preg_match('~name="_token"\s+value="([^"]+)"~', $g['b'], $m);
    return $m[1] ?? '';
};

/* ---- 1. Lead chung: /de-lai-thong-tin ---- */
echo "=== 1. LEAD CHUNG (/de-lai-thong-tin) ===\n";
$before = DB::table('contacts')->count();
$p = rq('POST', '/de-lai-thong-tin', [
    '_token' => $token('/'),
    'name'   => 'Kiem tra tu dong',
    'phone'  => '0912345679',
    'source' => 'trang-chu',
]);
$after = DB::table('contacts')->count();
echo "POST -> HTTP {$p['code']} | contacts: $before -> $after\n";
if (preg_match('~Location: (.*)~i', $p['h'], $l)) { echo "  Location: " . trim($l[1]) . "\n"; }
$row = DB::table('contacts')->orderByDesc('id')->first();
echo "  cuoi: id={$row->id} name='{$row->name}' phone='{$row->phone}' source='" . ($row->source ?? '') . "' status='" . ($row->status ?? '') . "'\n";

/* ---- 2. Lead gan nhan vien: /lien-he-tu-van ---- */
echo "\n=== 2. LEAD QUA NHAN VIEN (/lien-he-tu-van) ===\n";
$nv = DB::table('users')
    ->join('user_catalogues', 'users.user_catalogue_id', '=', 'user_catalogues.id')
    ->where('user_catalogues.is_sale', 1)
    ->where('users.publish', 2)
    ->whereNull('users.deleted_at')
    ->select('users.id', 'users.name')
    ->first();
echo "nhan vien kinh doanh: " . ($nv ? "#{$nv->id} {$nv->name}" : 'KHONG CO') . "\n";

if ($nv) {
    $before = DB::table('contacts')->count();
    $p2 = rq('POST', '/lien-he-tu-van', [
        '_token'       => $token('/cong-hoa/tu-van'),
        'name'         => 'Kiem tra tu van',
        'phone'        => '0912345680',
        'message'      => 'Du lieu kiem tra tu dong',
        'nhan_vien_id' => $nv->id,
    ], true);
    $after = DB::table('contacts')->count();
    $ok = preg_match('~nx_success|Đã gửi|đã gửi~iu', $p2['b']) ? 'co thong bao thanh cong' : 'khong thay thong bao';
    echo "POST -> HTTP {$p2['code']} | contacts: $before -> $after ($ok)\n";
    $row2 = DB::table('contacts')->orderByDesc('id')->first();
    echo "  cuoi: id={$row2->id} name='{$row2->name}' assigned_user_id='" . ($row2->assigned_user_id ?? '') . "'\n";
}

/* ---- 3. Don du lieu kiem tra ---- */
echo "\n=== 3. DON DU LIEU DO TEST TAO RA ===\n";
$deleted = DB::table('contacts')->whereIn('phone', ['0912345679', '0912345680', '0912345678'])->delete();
echo "da xoa $deleted ban ghi test\n";
echo "contacts con lai: " . DB::table('contacts')->count() . "\n";
