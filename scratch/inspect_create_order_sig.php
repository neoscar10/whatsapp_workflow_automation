<?php
require __DIR__ . '/../vendor/autoload.php';

use Cashfree\Cashfree;

$reflection = new ReflectionClass(Cashfree::class);
$method = $reflection->getMethod('PGCreateOrder');
echo "Name: " . $method->getName() . "\n";
echo "Parameters:\n";
foreach ($method->getParameters() as $param) {
    echo "- " . $param->getName() . " (type: " . ($param->getType() ? $param->getType()->getName() : 'none') . ")\n";
}
