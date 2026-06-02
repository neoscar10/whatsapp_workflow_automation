<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('demo_whatsapp_phone_number_id')->nullable()->constrained('whatsapp_phone_numbers')->onDelete('set null');
            $table->timestamp('demo_ends_at')->nullable()->after('demo_credits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['demo_whatsapp_phone_number_id']);
            $table->dropColumn(['demo_whatsapp_phone_number_id', 'demo_ends_at']);
        });
    }
};
