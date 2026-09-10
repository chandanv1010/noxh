<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$widgets = DB::table('widgets')->get();
foreach ($widgets as $w) {
    echo "ID: {$w->id} | Name: {$w->name} | Keyword: {$w->keyword} | Model: {$w->model}\n";
}
