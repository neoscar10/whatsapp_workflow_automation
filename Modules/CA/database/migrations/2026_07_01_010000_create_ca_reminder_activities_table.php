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
        // 1. Add whatsapp_template_id to ca_client_automations
        Schema::table('ca_client_automations', function (Blueprint $table) {
            $table->unsignedBigInteger('whatsapp_template_id')->nullable()->after('automation_library_id')->index();
            $table->foreign('whatsapp_template_id')->references('id')->on('whatsapp_templates')->nullOnDelete();
        });

        // 2. Create ca_reminder_activities
        Schema::create('ca_reminder_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('ca_client_automation_id')->constrained('ca_client_automations')->cascadeOnDelete();
            $table->foreignId('ca_client_automation_rule_id')->nullable()->constrained('ca_client_automation_rules')->nullOnDelete();
            $table->foreignId('ca_client_compliance_requirement_id')->constrained('ca_client_compliance_requirements', 'id', 'fk_cara_ccr_id')->cascadeOnDelete();
            $table->string('status')->default('scheduled'); // scheduled, queued, sent, delivered, read, failed
            $table->string('external_message_id')->nullable()->index();
            $table->text('error_message')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ca_reminder_activities');

        Schema::table('ca_client_automations', function (Blueprint $table) {
            $table->dropForeign(['whatsapp_template_id']);
            $table->dropColumn('whatsapp_template_id');
        });
    }
};
