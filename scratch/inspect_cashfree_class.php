<?php
require __DIR__ . '/../vendor/autoload.php';

use Cashfree\Cashfree;

$reflection = new ReflectionClass(Cashfree::class);
echo "Properties:\n";
foreach ($reflection->getProperties() as $prop) {
    echo "- " . $prop->getName() . " (static: " . ($prop->isStatic() ? 'yes' : 'no') . ")\n";
}
echo "Constants:\n";
foreach ($reflection->getConstants() as $name => $val) {
    echo "- " . $name . " = " . print_r($val, true) . "\n";
}
