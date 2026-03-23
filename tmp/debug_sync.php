<?php
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;

$account = WhatsAppAccount::first();
if ($account) {
    echo "Account ID: " . $account->id . "\n";
    echo "Company ID: " . $account->company_id . "\n";
    echo "WABA ID: " . $account->waba_id . "\n";
    echo "Status: " . $account->connection_status . "\n";
    echo "Last Error: " . ($account->last_sync_error ?? 'None') . "\n";
    echo "Last Synced: " . ($account->last_synced_at ?? 'Never') . "\n";
    
    $numbers = WhatsAppPhoneNumber::where('whatsapp_account_id', $account->id)->get();
    echo "\nNumbers count: " . $numbers->count() . "\n";
    foreach ($numbers as $n) {
        echo "- ID: {$n->phone_number_id}, Number: '{$n->phone_number}', Status: {$n->status}, Error: '{$n->last_sync_error}'\n";
    }
} else {
    echo "No WhatsApp account found.\n";
}
