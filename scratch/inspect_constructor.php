<?php
require __DIR__ . '/../vendor/autoload.php';

use Cashfree\Cashfree;

$reflection = new ReflectionClass(Cashfree::class);
$ctor = $reflection->getConstructor();
echo "Constructor parameters:\n";
foreach ($ctor->getParameters() as $param) {
    echo "- " . $param->getName() . " (hasDefault: " . ($param->isDefaultValueAvailable() ? 'yes' : 'no') . ", type: " . ($param->getType() ? $param->getType()->getName() : 'none') . ")\n";
}
