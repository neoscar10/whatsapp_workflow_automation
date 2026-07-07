<?php
$m = App\Models\Chat\ConversationMessage::where('message_type','template')
    ->where('direction','outbound')
    ->latest()
    ->first();

echo "=== META PAYLOAD HAS COMPONENTS? ===\n";
$components = $m->meta_payload['components'] ?? 'NOT FOUND';
echo is_array($components) ? "YES - " . count($components) . " component(s)\n" : $components . "\n";
echo "\n";

echo "=== RENDERED BODY ===\n";
echo $m->rendered_body . "\n";
