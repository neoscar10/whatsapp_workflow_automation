<?php
function testReturn(): string {
    return ['an', 'array'];
}

try {
    testReturn();
} catch (Throwable $e) {
    echo "Return type error: " . get_class($e) . " - " . $e->getMessage() . "\n";
}
