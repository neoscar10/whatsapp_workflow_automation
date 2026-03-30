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
        Schema::create('automation_simulation_breakpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_flow_id')->constrained('automation_flows')->onDelete('cascade');
            $table->string('node_id');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['automation_flow_id', 'node_id'], 'flow_node_breakpoint_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_simulation_breakpoints');
    }
};
