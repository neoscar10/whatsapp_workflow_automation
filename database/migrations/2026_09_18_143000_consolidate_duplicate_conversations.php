<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find duplicate conversations per company and phone number / contact
        $conversations = DB::table('conversations')
            ->orderBy('id', 'asc')
            ->get();

        $grouped = [];
        foreach ($conversations as $conv) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $conv->contact_phone ?? '');
            $last10 = strlen($cleanPhone) >= 10 ? substr($cleanPhone, -10) : $cleanPhone;
            $key = $conv->company_id . '_' . ($conv->contact_id ?: ($last10 ?: $cleanPhone));

            if (empty($key) || $key === ($conv->company_id . '_')) {
                continue;
            }

            if (!isset($grouped[$key])) {
                $grouped[$key] = $conv->id;
            } else {
                $primaryId = $grouped[$key];
                $duplicateId = $conv->id;

                // Move all messages to primary conversation
                DB::table('conversation_messages')
                    ->where('conversation_id', $duplicateId)
                    ->update(['conversation_id' => $primaryId]);

                // Move all notes to primary conversation
                DB::table('conversation_notes')
                    ->where('conversation_id', $duplicateId)
                    ->update(['conversation_id' => $primaryId]);

                // Delete duplicate conversation
                DB::table('conversations')
                    ->where('id', $duplicateId)
                    ->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data consolidation is non-reversible
    }
};
