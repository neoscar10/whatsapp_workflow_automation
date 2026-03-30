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
        Schema::create('automation_simulation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_flow_id')->constrained('automation_flows')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('status')->default('ready'); // ready, running, paused, completed, failed, stopped
            $table->string('current_node_id')->nullable();
            $table->json('context')->nullable(); // runtime variables context
            $table->json('initial_payload')->nullable(); // trigger input
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_simulation_sessions');
    }
};
