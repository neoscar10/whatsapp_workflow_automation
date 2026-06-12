<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\WhatsApp\WhatsAppTemplateVariableResolver;
use App\Models\User;

$resolver = new WhatsAppTemplateVariableResolver();
$user = User::with('company')->first();

$resolved = $resolver->resolve('company_name', null, $user);
echo "Type of resolved: " . gettype($resolved) . "\n";
var_dump($resolved);

$result = $resolver->resolveAllForPreview(
    "Hello {{1}}",
    [
        'body:1' => [
            'type' => 'system',
            'value' => 'company_name',
            'name' => '1',
            'component' => 'body'
        ]
    ],
    null,
    $user
);

echo "Result: " . $result . "\n";
