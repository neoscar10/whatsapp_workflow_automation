<?php
$files = [
    'resources/views/livewire/web/whatsapp/template-create-page.blade.php',
    'resources/views/livewire/web/whatsapp/template-edit-page.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Normalize line endings to LF
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        // Ensure no weird NULL characters (happens with UTF-16 misreads)
        $content = str_replace("\0", "", $content);
        file_put_contents($file, $content);
        echo "Normalized $file\n";
    }
}
