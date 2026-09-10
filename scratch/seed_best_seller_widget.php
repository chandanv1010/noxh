<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$existing = DB::table('widgets')->where('keyword', 'best-seller-products')->first();
if ($existing) {
    DB::table('widgets')->where('keyword', 'best-seller-products')->update([
        'name' => 'Sản phẩm bán chạy',
        'model' => 'Product',
        'model_id' => json_encode(["1", "2", "3", "4", "5"]),
        'publish' => 2,
        'updated_at' => now(),
    ]);
    echo "Updated existing best-seller-products widget.\n";
} else {
    DB::table('widgets')->insert([
        'name' => 'Sản phẩm bán chạy',
        'keyword' => 'best-seller-products',
        'model' => 'Product',
        'model_id' => json_encode(["1", "2", "3", "4", "5"]),
        'short_code' => 'best-seller-products',
        'publish' => 2,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created new best-seller-products widget.\n";
}
