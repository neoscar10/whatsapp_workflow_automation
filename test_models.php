<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = env('GEMINI_API_KEY');
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $key;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
$data = json_decode($res, true);
foreach ($data['models'] as $model) {
    echo $model['name'] . "\n";
}
