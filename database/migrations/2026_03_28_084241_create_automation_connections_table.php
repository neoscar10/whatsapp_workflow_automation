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
        Schema::create('automation_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_flow_id')->constrained('automation_flows')->cascadeOnDelete();
            $table->foreignId('source_node_id')->constrained('automation_nodes')->cascadeOnDelete();
            $table->foreignId('target_node_id')->constrained('automation_nodes')->cascadeOnDelete();
            $table->string('source_handle')->nullable();
            $table->string('target_handle')->nullable();
            $table->string('condition_key')->nullable(); // yes, no, etc.
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automation_connections');
    }
};
