<?php
/** Xem bang slides va cac slide dang co, cung nhu cach frontend lay slide. */

$root = 'D:/sandbox/noxh';
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

echo "=== CAU TRUC BANG slides ===\n";
foreach (DB::select('SHOW COLUMNS FROM slides') as $c) {
    printf("  %-14s %-22s null=%s default=%s\n", $c->Field, $c->Type, $c->Null, var_export($c->Default, true));
}

echo "\n=== DU LIEU TRONG slides ===\n";
$rows = DB::table('slides')->get();
if ($rows->isEmpty()) {
    echo "  (rong)\n";
}
foreach ($rows as $r) {
    printf("  id=%s keyword=%-14s name=%-28s publish=%s\n", $r->id, $r->keyword, $r->name ?? '', $r->publish ?? '');
    $item = json_decode($r->item ?? '[]', true);
    if (is_array($item)) {
        foreach ($item as $lang => $ds) {
            printf("      lang %s: %d anh\n", $lang, is_array($ds) ? count($ds) : 0);
            if (is_array($ds)) {
                foreach (array_slice($ds, 0, 3) as $a) {
                    printf("        - name=%s image=%s\n", $a['name'] ?? '', $a['image'] ?? '');
                }
            }
        }
    }
}

echo "\n=== NoxhComposer lay slide nhu the nao ===\n";
$src = file_get_contents($root . '/app/Providers/LanguageComposerServiceProvider.php');
$dong = explode("\n", $src);
foreach ($dong as $i => $l) {
    if (str_contains($l, 'Slide') || str_contains($l, 'slides')) {
        printf("  %4d: %s\n", $i + 1, trim($l));
    }
}

echo "\n=== Trang chu co dung bien \$slides khong? ===\n";
foreach (['home/index', 'component/header', 'component/footer'] as $v) {
    $f = $root . '/resources/views/frontend/noxh/' . $v . '.blade.php';
    if (! is_file($f)) { continue; }
    $n = substr_count(file_get_contents($f), 'slides');
    printf("  %-24s %d lan nhac 'slides'\n", $v, $n);
}
