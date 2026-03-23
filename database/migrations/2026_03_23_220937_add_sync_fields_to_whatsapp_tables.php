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
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->timestamp('last_synced_at')->nullable()->after('last_verified_at');
            $table->text('last_sync_error')->nullable()->after('last_synced_at');
        });

        Schema::table('whatsapp_phone_numbers', function (Blueprint $table) {
            $table->string('verified_name')->nullable()->after('display_name');
            $table->string('quality_rating')->nullable()->after('status');
            $table->string('code_verification_status')->nullable()->after('quality_rating');
            $table->timestamp('synced_at')->nullable()->after('code_verification_status');
            $table->text('last_sync_error')->nullable()->after('synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->dropColumn(['last_synced_at', 'last_sync_error']);
        });

        Schema::table('whatsapp_phone_numbers', function (Blueprint $table) {
            $table->dropColumn(['verified_name', 'quality_rating', 'code_verification_status', 'synced_at', 'last_sync_error']);
        });
    }
};
