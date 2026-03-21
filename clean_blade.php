<?php
$files = [
    'resources/views/livewire/web/whatsapp/template-create-page.blade.php',
    'resources/views/livewire/web/whatsapp/template-edit-page.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Detect and convert if it's UTF-16
        if (strpos($content, "\xFF\xFE") === 0 || strpos($content, "\xFE\xFF") === 0) {
             $content = mb_convert_encoding($content, 'UTF-8', 'UTF-16');
        }
        file_put_contents($file, $content);
        echo "Cleaned $file\n";
    }
}
