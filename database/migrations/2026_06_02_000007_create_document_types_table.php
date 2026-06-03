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
        Schema::create('document_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('verification_template_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('placeholder')->nullable();
            $table->string('accepted_formats')->default('pdf,jpg,png,jpeg');
            $table->integer('max_size_mb')->default(10);
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Constraints
            $table->foreign('verification_template_id')
                ->references('id')
                ->on('verification_templates')
                ->cascadeOnDelete();

            // Indexes
            $table->index(['verification_template_id', 'is_active', 'sort_order'], 'doc_types_template_active_order_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
