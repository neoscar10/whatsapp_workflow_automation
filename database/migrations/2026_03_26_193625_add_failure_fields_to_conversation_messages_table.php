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
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->timestamp('failed_at')->nullable()->after('read_at');
            $table->string('failure_code')->nullable()->after('failed_at');
            $table->text('failure_message')->nullable()->after('failure_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversation_messages', function (Blueprint $table) {
            $table->dropColumn(['failed_at', 'failure_code', 'failure_message']);
        });
    }
};
