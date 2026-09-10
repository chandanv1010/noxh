<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Frontend\SitemapController;
use Illuminate\Http\Request;

$controller = new SitemapController();
$response = $controller->index(new Request());

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->headers->get('Content-Type') . "\n";
echo "XML Preview (First 500 chars):\n";
echo substr($response->getContent(), 0, 500) . "\n";
