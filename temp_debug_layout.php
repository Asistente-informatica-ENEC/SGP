<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/assets', 'GET');
$app->instance('request', $request);
$app->instance(Illuminate\Http\Request::class, $request);
$screen = new App\Orchid\Screens\Asset\AssetListScreen();
$layout = $screen->layout();
echo 'COUNT: ' . count($layout) . "\n";
foreach ($layout as $item) {
    echo get_class($item) . "\n";
}
