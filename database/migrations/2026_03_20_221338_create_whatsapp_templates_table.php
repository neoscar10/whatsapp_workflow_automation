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
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('whatsapp_account_id')->constrained('whatsapp_accounts')->cascadeOnDelete();
            
            // Remote Info
            $table->string('remote_template_id')->nullable()->index();
            $table->string('remote_template_name')->index();
            $table->string('display_title')->nullable();
            
            // Classification & Status
            $table->string('category'); // marketing, utility, authentication
            $table->string('language_code'); // e.g. en_US
            $table->string('status'); // draft, submitted, pending, approved, rejected, etc.
            $table->string('quality_rating')->nullable(); // high, medium, low
            $table->string('namespace')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Content
            $table->string('header_type')->nullable(); // none, text, image, video, document
            $table->string('header_text')->nullable();
            $table->longText('body_text');
            $table->string('footer_text')->nullable();
            $table->unsignedInteger('button_count')->default(0);
            
            // Payloads
            $table->json('example_payload')->nullable();
            $table->json('meta_payload')->nullable();
            
            // Timestamps
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
