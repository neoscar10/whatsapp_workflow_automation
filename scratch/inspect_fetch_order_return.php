<?php
require __DIR__ . '/../vendor/autoload.php';

use Cashfree\Cashfree;

$reflection = new ReflectionClass(Cashfree::class);
$method = $reflection->getMethod('PGFetchOrder');
echo $method->getDocComment() . "\n";
