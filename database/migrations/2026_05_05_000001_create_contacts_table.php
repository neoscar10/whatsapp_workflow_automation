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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_phone_number_id')->nullable()->constrained('whatsapp_phone_numbers')->nullOnDelete();
            $table->string('name')->nullable();
            $table->string('phone');
            $table->string('normalized_phone');
            $table->string('email')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('source')->default('manual'); // manual, inbound_chat, import, api, campaign, webhook
            $table->string('status')->default('active'); // active, inactive, blocked, archived
            $table->boolean('has_opted_in')->default(false);
            $table->timestamp('opted_in_at')->nullable();
            $table->string('opted_in_source')->nullable();
            $table->timestamp('opted_out_at')->nullable();
            $table->boolean('do_not_message')->default(false);
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamp('last_inbound_at')->nullable();
            $table->timestamp('last_outbound_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'normalized_phone']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'source']);
            $table->index(['company_id', 'last_interaction_at']);
            $table->index(['company_id', 'do_not_message']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
