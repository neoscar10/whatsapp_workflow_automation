<?php
require __DIR__ . '/../vendor/autoload.php';

use Cashfree\Cashfree;

$reflection = new ReflectionClass(Cashfree::class);
echo "Constants:\n";
print_r($reflection->getConstants());

echo "Static Properties:\n";
foreach ($reflection->getProperties(ReflectionProperty::IS_STATIC) as $prop) {
    echo "- " . $prop->getName() . "\n";
}
