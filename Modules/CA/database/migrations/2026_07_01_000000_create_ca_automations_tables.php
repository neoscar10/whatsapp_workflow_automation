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
        // 1. ca_automation_library
        Schema::create('ca_automation_library', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('frequency');
            $table->text('description')->nullable();
            $table->string('ai_prompt_version')->default('1.0');
            $table->string('default_template_reference')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        // 2. ca_client_automations
        Schema::create('ca_client_automations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->index();
            $table->foreignId('client_id')->constrained('ca_clients')->cascadeOnDelete();
            $table->foreignId('automation_library_id')->constrained('ca_automation_library')->cascadeOnDelete();
            $table->string('frequency');
            $table->string('status')->default('active');
            $table->boolean('is_enabled')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        // 3. ca_client_automation_documents
        Schema::create('ca_client_automation_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_automation_id')
                ->constrained('ca_client_automations')
                ->cascadeOnDelete();
            $table->foreignId('ca_client_compliance_requirement_id')
                ->constrained('ca_client_compliance_requirements', 'id', 'fk_ccad_ccr_id')
                ->cascadeOnDelete();
            $table->timestamps();
        });

        // 4. ca_client_automation_rules
        Schema::create('ca_client_automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_automation_id')
                ->constrained('ca_client_automations')
                ->cascadeOnDelete();
            $table->string('trigger_type'); // before_due, on_due, after_due
            $table->integer('offset_days')->default(0);
            $table->string('send_time');
            $table->integer('sequence')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        // 5. ca_ai_automation_templates
        Schema::create('ca_ai_automation_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_library_id')->constrained('ca_automation_library')->cascadeOnDelete();
            $table->string('frequency');
            $table->string('language')->default('en');
            $table->string('tone')->default('professional');
            $table->string('message_title')->nullable();
            $table->text('message_body');
            $table->string('prompt_version')->default('1.0');
            $table->string('ai_provider');
            $table->string('ai_model');
            $table->string('cache_version')->default('1.0');
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ca_ai_automation_templates');
        Schema::dropIfExists('ca_client_automation_rules');
        Schema::dropIfExists('ca_client_automation_documents');
        Schema::dropIfExists('ca_client_automations');
        Schema::dropIfExists('ca_automation_library');
    }
};
