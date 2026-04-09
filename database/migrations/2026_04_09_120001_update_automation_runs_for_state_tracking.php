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
        Schema::table('automation_runs', function (Blueprint $table) {
            $table->foreignId('current_node_id')->nullable()->after('status')->constrained('automation_nodes')->nullOnDelete();
            $table->json('context')->nullable()->after('trigger_context');
            $table->integer('step_count')->default(0)->after('context');
            $table->text('last_error')->nullable()->after('metadata');
            
            // Note: status already exists, we will use it for: running, completed, failed, delayed, queued
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automation_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_node_id');
            $table->dropColumn(['context', 'step_count', 'last_error']);
        });
    }
};
