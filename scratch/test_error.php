<?php
$placeholder = ['an', 'array'];
try {
    $text = "Hello";
    $value = "World";
    $text = str_replace("{{{$placeholder}}}", $value ?: "{{{$placeholder}}}", $text);
} catch (Throwable $e) {
    echo "Placeholder array error: " . $e->getMessage() . "\n";
}

$value = ['an', 'array'];
try {
    $text = "Hello";
    $placeholder = "name";
    $text = str_replace("{{{$placeholder}}}", $value ?: "{{{$placeholder}}}", $text);
} catch (Throwable $e) {
    echo "Value array error: " . $e->getMessage() . "\n";
}

$text = ['an', 'array'];
try {
    $placeholder = "name";
    $value = "World";
    $text = str_replace("{{{$placeholder}}}", $value ?: "{{{$placeholder}}}", $text);
} catch (Throwable $e) {
    echo "Text array error: " . $e->getMessage() . "\n";
}
