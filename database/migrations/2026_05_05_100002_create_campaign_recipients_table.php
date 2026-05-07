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
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('conversations')->nullOnDelete();
            $table->foreignId('conversation_message_id')->nullable()->constrained('conversation_messages')->nullOnDelete();
            
            $table->string('phone');
            $table->string('normalized_phone');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            
            $table->string('source')->default('contact'); // contact, imported, manual
            $table->string('status')->default('pending'); // pending, queued, sending, sent, delivered, read, failed, skipped, cancelled
            
            $table->string('skip_reason')->nullable();
            
            $table->json('personalization_data')->nullable();
            $table->json('resolved_template_payload')->nullable();
            
            $table->string('provider_message_id')->nullable();
            
            $table->string('meta_error_code')->nullable();
            $table->text('meta_error_message')->nullable();
            $table->json('meta_error_payload')->nullable();
            
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            
            $table->timestamps();

            // Constraints & Indexes
            $table->unique(['campaign_id', 'normalized_phone']);
            $table->index(['campaign_id', 'status']);
            $table->index(['company_id', 'status']);
            $table->index(['contact_id']);
            $table->index(['normalized_phone']);
            $table->index(['provider_message_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_recipients');
    }
};
