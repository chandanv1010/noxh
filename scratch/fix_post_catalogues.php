<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$affected = DB::table('posts')
    ->where('post_catalogue_id', 0)
    ->update(['post_catalogue_id' => 1]);

echo "Updated posts with catalogue 0 to 1. Affected rows: " . $affected . "\n";
