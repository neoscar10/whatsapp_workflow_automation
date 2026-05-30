<?php
require __DIR__ . '/../vendor/autoload.php';

use Cashfree\Cashfree;

$reflection = new ReflectionClass(Cashfree::class);
foreach ($reflection->getMethods() as $method) {
    if (stripos($method->getName(), 'Payment') !== false || stripos($method->getName(), 'Order') !== false) {
        echo "- " . $method->getName() . "\n";
    }
}
