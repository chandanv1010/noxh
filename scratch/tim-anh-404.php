<?php
/**
 * Tim cac duong dan anh dang tro toi tep khong ton tai.
 *
 * Chay: php scratch/tim-anh-404.php
 *
 * Trang quan tri bao 404 cho /userfiles/image/language/Flag_of_Vietnam_svg.png
 * va /userfiles/image/1750518865_6856cc51d7a38.png. Script nay tim xem khoa nao
 * trong CSDL dang giu hai duong dan do.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== BANG languages ===\n";
foreach (DB::table('languages')->get() as $l) {
    printf("  id=%s  name=%-12s  image=%s  publish=%s\n", $l->id, $l->name, $l->image, $l->publish);
    $duong = public_path(ltrim(str_replace('/public/', '/', (string) $l->image), '/'));
    printf("        tep that: %s  -> %s\n", $duong, is_file($duong) ? 'CO' : 'THIEU');
}

echo "\n=== O cau hinh nao dang giu duong dan /userfiles ===\n";
$tim = 0;
foreach (['introduces', 'configurations', 'slides', 'routers', 'post_catalogues', 'product_catalogues'] as $bang) {
    if (!DB::getSchemaBuilder()->hasTable($bang)) continue;
    $cot = DB::getSchemaBuilder()->getColumnListing($bang);
    $co = array_values(array_intersect($cot, ['content', 'item', 'image', 'album', 'description', 'value']));
    if (!$co) continue;
    foreach (DB::table($bang)->get() as $row) {
        foreach ($co as $c) {
            $v = (string) ($row->$c ?? '');
            if ($v === '' || strpos($v, 'userfiles') === false) continue;
            preg_match_all('#/userfiles/[^"\'\\\\ )\]]+#', $v, $m);
            foreach (array_unique($m[0]) as $d) {
                $tep = public_path(ltrim($d, '/'));
                printf("  %-16s id=%-6s khoa=%-28s %s -> %s\n",
                    $bang, $row->id ?? '?', ($row->keyword ?? $row->name ?? '?'), $d, is_file($tep) ? 'CO' : 'THIEU');
                $tim++;
            }
        }
    }
}
if ($tim === 0) echo "  (khong thay)\n";
echo "\nTong so duong dan /userfiles tim thay: $tim\n";
