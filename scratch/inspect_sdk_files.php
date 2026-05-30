<?php
require __DIR__ . '/../vendor/autoload.php';

$dir = new RecursiveDirectoryIterator(__DIR__ . '/../vendor/cashfree/cashfree-pg/lib');
$iterator = new RecursiveIteratorIterator($dir);
$nonPhp = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        if (!str_starts_with(trim($content), '<?php')) {
            $nonPhp[] = [
                'path' => $file->getPathname(),
                'preview' => substr($content, 0, 100)
            ];
        }
    }
}

echo "Non-PHP files with .php extension:\n";
print_r($nonPhp);
