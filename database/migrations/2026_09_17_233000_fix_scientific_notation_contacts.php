<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Support\PhoneNumberNormalizer;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix contacts with scientific notation in phone or name
        DB::table('contacts')->orderBy('id')->chunk(200, function ($contacts) {
            foreach ($contacts as $contact) {
                $hasScientificPhone = is_numeric($contact->phone ?? '') && preg_match('/[eE]/', $contact->phone);
                $hasScientificName = is_numeric($contact->name ?? '') && preg_match('/[eE]/', $contact->name);
                $hasCorruptedNormalized = is_numeric($contact->normalized_phone ?? '') && preg_match('/[eE]/', $contact->normalized_phone);

                if ($hasScientificPhone || $hasScientificName || $hasCorruptedNormalized) {
                    $cleanPhone = PhoneNumberNormalizer::clean($contact->phone ?? '');
                    $normalizedPhone = PhoneNumberNormalizer::normalize($cleanPhone);
                    $cleanName = $contact->name;

                    if ($hasScientificName || $cleanName === $contact->phone || empty($cleanName)) {
                        $cleanName = $cleanPhone;
                    }

                    DB::table('contacts')
                        ->where('id', $contact->id)
                        ->update([
                            'phone' => $cleanPhone,
                            'normalized_phone' => $normalizedPhone,
                            'name' => $cleanName,
                        ]);
                }
            }
        });

        // Fix campaign recipients with scientific notation
        DB::table('campaign_recipients')->orderBy('id')->chunk(200, function ($recipients) {
            foreach ($recipients as $recipient) {
                $hasScientificPhone = is_numeric($recipient->phone ?? '') && preg_match('/[eE]/', $recipient->phone);
                $hasScientificName = is_numeric($recipient->name ?? '') && preg_match('/[eE]/', $recipient->name);

                if ($hasScientificPhone || $hasScientificName) {
                    $cleanPhone = PhoneNumberNormalizer::clean($recipient->phone ?? '');
                    $normalizedPhone = PhoneNumberNormalizer::normalize($cleanPhone);
                    $cleanName = $recipient->name;

                    if ($hasScientificName || $cleanName === $recipient->phone || empty($cleanName)) {
                        $cleanName = $cleanPhone;
                    }

                    DB::table('campaign_recipients')
                        ->where('id', $recipient->id)
                        ->update([
                            'phone' => $cleanPhone,
                            'normalized_phone' => $normalizedPhone,
                            'name' => $cleanName,
                        ]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data cleanup is non-reversible
    }
};
