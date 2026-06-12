<?php
$log = file_get_contents(__DIR__ . '/../storage/logs/laravel.log');
preg_match_all('/Array to string conversion.*?WhatsAppTemplateVariableResolver\.php:50.*?(?=\[2026-06)/s', $log, $matches);
if (!empty($matches[0])) {
    echo $matches[0][count($matches[0]) - 1];
} else {
    echo "No match found.\n";
}
