<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$products = DB::table('products')
    ->join('product_language', 'products.id', '=', 'product_language.product_id')
    ->where('product_language.language_id', 1)
    ->select('products.id', 'product_language.name')
    ->get();

foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->name}\n";
}
