<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\V1\Core\WidgetService;

$widgetService = app(WidgetService::class);
$widgets = $widgetService->getWidget([
    ['keyword' => 'best-seller-products']
], 1);

$widget = $widgets['best-seller-products'];
echo "Widget Name: " . $widget->name . "\n";
echo "Object count: " . ($widget->object ? $widget->object->count() : 0) . "\n";

if ($widget->object && $widget->object->isNotEmpty()) {
    $firstItem = $widget->object->first();
    echo "Item Class: " . get_class($firstItem) . " (Wait, if it's stdClass, class is " . gettype($firstItem) . ")\n";
    echo "Languages type: " . gettype($firstItem->languages) . "\n";
    if (is_object($firstItem->languages)) {
        echo "Languages Class: " . get_class($firstItem->languages) . "\n";
    }
}
