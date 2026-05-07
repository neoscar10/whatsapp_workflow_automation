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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->foreignId('whatsapp_phone_number_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('whatsapp_template_id')->nullable()->constrained()->nullOnDelete();
            
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            
            $table->string('type')->default('template'); // template, text
            $table->string('status')->default('draft'); // draft, scheduled, queued, sending, paused, completed, cancelled, failed
            
            $table->string('audience_type')->default('selected_contacts'); // selected_contacts, tags, groups, filters, imported, mixed
            $table->json('audience_filters')->nullable();
            
            $table->longText('message_body')->nullable();
            
            // Template Snapshot
            $table->string('template_name')->nullable();
            $table->string('template_language')->nullable();
            $table->json('template_components')->nullable();
            $table->json('template_variable_mapping')->nullable();
            $table->json('default_variable_values')->nullable();
            
            $table->json('personalization_config')->nullable();
            
            // Aggregated Stats
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('eligible_recipient_count')->default(0);
            $table->unsignedInteger('skipped_recipient_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedInteger('read_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('pending_count')->default(0);
            
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('last_dispatched_at')->nullable();
            
            $table->text('failure_reason')->nullable();
            $table->json('meta')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'scheduled_at']);
            $table->index(['company_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
