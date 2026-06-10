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
        Schema::table('ca_ai_cache', function (Blueprint $table) {
            $table->string('provider_name')->default('openai')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ca_ai_cache', function (Blueprint $table) {
            $table->dropColumn('provider_name');
        });
    }
};
