<?php
require __DIR__ . '/../vendor/autoload.php';

use Cashfree\Cashfree;

echo "Cashfree class exists: " . (class_exists(Cashfree::class) ? 'YES' : 'NO') . "\n";

$reflection = new ReflectionClass(Cashfree::class);
echo "Methods:\n";
foreach ($reflection->getMethods() as $method) {
    echo "- " . $method->getName() . "\n";
}
