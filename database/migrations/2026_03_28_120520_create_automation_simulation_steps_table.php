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
        Schema::create('automation_simulation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulation_session_id')->constrained('automation_simulation_sessions')->onDelete('cascade');
            $table->string('node_id');
            $table->string('node_type');
            $table->string('node_subtype')->nullable();
            $table->string('status')->default('queued'); // queued, running, success, failed, breakpoint, waiting
            $table->json('input_snapshot')->nullable();
            $table->json('output_snapshot')->nullable();
            $table->text('log_message')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_simulation_steps');
    }
};
