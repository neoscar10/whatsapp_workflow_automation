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
        Schema::table('automation_flows', function (Blueprint $table) {
            $table->string('builder_version')->nullable()->after('is_enabled');
            $table->json('canvas_meta')->nullable()->after('builder_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automation_flows', function (Blueprint $table) {
            $table->dropColumn(['builder_version', 'canvas_meta']);
        });
    }
};
