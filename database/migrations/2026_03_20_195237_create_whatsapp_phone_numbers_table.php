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
        Schema::create('whatsapp_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->index()->constrained()->onDelete('cascade');
            $table->foreignId('whatsapp_account_id')->nullable()->constrained()->onDelete('set null');
            $table->string('display_name');
            $table->string('phone_number_id');
            $table->string('phone_number')->nullable();
            $table->string('status')->default('active')->index();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['company_id', 'phone_number_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_phone_numbers');
    }
};
