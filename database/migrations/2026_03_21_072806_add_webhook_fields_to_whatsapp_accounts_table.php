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
            $table->string('webhook_callback_url')->nullable()->after('webhook_status');
            $table->string('webhook_verify_token')->nullable()->after('webhook_callback_url');
            $table->timestamp('webhook_verified_at')->nullable()->after('webhook_verify_token');
            $table->timestamp('webhook_last_checked_at')->nullable()->after('webhook_verified_at');
            $table->text('webhook_last_error')->nullable()->after('webhook_last_checked_at');
            $table->string('webhook_subscription_status')->default('not_subscribed')->after('webhook_last_error');
            $table->timestamp('webhook_subscribed_at')->nullable()->after('webhook_subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'webhook_callback_url',
                'webhook_verify_token',
                'webhook_verified_at',
                'webhook_last_checked_at',
                'webhook_last_error',
                'webhook_subscription_status',
                'webhook_subscribed_at',
            ]);
        });
    }
};
