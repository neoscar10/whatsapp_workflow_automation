<?php
require __DIR__ . '/../vendor/autoload.php';

use Cashfree\Model\PaymentEntity;

$reflection = new ReflectionClass(PaymentEntity::class);
echo "Class: " . $reflection->getName() . "\n";
echo "Methods:\n";
foreach ($reflection->getMethods() as $method) {
    if (str_starts_with($method->getName(), 'get')) {
        echo "- " . $method->getName() . "\n";
    }
}
