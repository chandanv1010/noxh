<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$catalogues = DB::table('post_catalogues')
    ->join('post_catalogue_language', 'post_catalogue_language.post_catalogue_id', '=', 'post_catalogues.id')
    ->get();

print_r($catalogues);
