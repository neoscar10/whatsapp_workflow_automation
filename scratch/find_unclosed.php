<?php

$path = __DIR__ . '/../resources/views/livewire/campaigns/campaign-form-modal.blade.php';
$lines = file($path);

$ifs = [];
$foreaches = [];
$forelses = [];

foreach ($lines as $i => $line) {
    $lineNum = $i + 1;
    
    // Check @if
    if (preg_match('/@if\s*\(/', $line)) {
        $ifs[] = ['line' => $lineNum, 'content' => trim($line)];
    }
    if (preg_match('/@endif/', $line)) {
        if (!empty($ifs)) {
            array_pop($ifs);
        } else {
            echo "UNMATCHED @endif at line {$lineNum}\n";
        }
    }
}

echo "=== UNCLOSED @if DIRECTIVES (" . count($ifs) . ") ===\n";
foreach ($ifs as $item) {
    echo "Line {$item['line']}: {$item['content']}\n";
}
