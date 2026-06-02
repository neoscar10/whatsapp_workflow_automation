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
        Schema::create('funding_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('amount', 16, 4);
            $table->decimal('text_rate', 8, 4);
            $table->decimal('template_utility_rate', 8, 4);
            $table->decimal('template_auth_rate', 8, 4);
            $table->decimal('template_marketing_rate', 8, 4);
            $table->decimal('automation_rate', 8, 4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funding_packages');
    }
};
