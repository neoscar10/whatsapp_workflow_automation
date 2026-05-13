<?php
$content = file_get_contents('resources/views/livewire/campaigns/campaign-form-modal.blade.php');
$lines = explode("\n", $content);
$stack = [];
foreach ($lines as $i => $line) {
    if (preg_match('/@(if|foreach|forelse|php|while|for)\b/', $line, $m)) {
        if ($m[1] == 'php' && strpos($line, '@php') !== false && strpos($line, '@endphp') !== false) continue;
        $stack[] = ['type' => $m[1], 'line' => $i + 1];
    }
    if (preg_match('/@(endif|endforeach|endforelse|endphp|endwhile|endfor)\b/', $line, $m)) {
        $last = array_pop($stack);
        echo "Line " . ($i + 1) . ": " . $m[0] . " closes " . ($last ? $last['type'] . " at line " . $last['line'] : "NOTHING!") . "\n";
    }
}
if (!empty($stack)) {
    echo "UNCLOSED DIRECTIVES:\n";
    foreach($stack as $s) {
        echo $s['type'] . " at line " . $s['line'] . "\n";
    }
}
