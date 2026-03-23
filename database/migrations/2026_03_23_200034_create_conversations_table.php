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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_phone_number_id')->nullable()->constrained('whatsapp_phone_numbers')->nullOnDelete();
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('contact_avatar_url')->nullable();
            $table->string('contact_location')->nullable();
            $table->string('status')->default('open'); // open|closed
            $table->string('assignment_status')->default('unassigned'); // assigned|unassigned
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->text('last_message_preview')->nullable();
            $table->integer('unread_count')->default(0);
            $table->json('labels')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
