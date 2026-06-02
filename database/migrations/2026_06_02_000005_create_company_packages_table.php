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
        Schema::create('company_packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->uuid('payment_transaction_id')->nullable();
            $table->decimal('amount', 16, 4);
            $table->decimal('remaining_balance', 16, 4);
            $table->decimal('text_rate', 8, 4);
            $table->decimal('template_utility_rate', 8, 4);
            $table->decimal('template_auth_rate', 8, 4);
            $table->decimal('template_marketing_rate', 8, 4);
            $table->decimal('automation_rate', 8, 4);
            $table->string('status')->default('active'); // active, consumed, expired
            $table->timestamps();

            // Index
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_packages');
    }
};
