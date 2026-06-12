<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ca_compliance_requirements
        Schema::table('ca_compliance_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('ca_compliance_requirements', 'document_type')) {
                $table->string('document_type')->nullable()->after('input_type');
            }
            if (!Schema::hasColumn('ca_compliance_requirements', 'required_stage')) {
                // If it already has required_when, we might leave it or add required_stage
                $table->string('required_stage')->default('onboarding')->after('is_recurring');
            }
            if (!Schema::hasColumn('ca_compliance_requirements', 'validation_notes')) {
                $table->text('validation_notes')->nullable()->after('required_stage');
            }
            if (!Schema::hasColumn('ca_compliance_requirements', 'metadata_json')) {
                $table->json('metadata_json')->nullable()->after('status');
            }
        });

        // 2. ca_client_compliance_requirements
        Schema::table('ca_client_compliance_requirements', function (Blueprint $table) {
            if (!Schema::hasColumn('ca_client_compliance_requirements', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('is_required');
            }
            if (!Schema::hasColumn('ca_client_compliance_requirements', 'required_stage')) {
                $table->string('required_stage')->default('onboarding')->after('is_recurring');
            }
            if (!Schema::hasColumn('ca_client_compliance_requirements', 'recurrence_frequency')) {
                $table->string('recurrence_frequency')->nullable()->after('required_stage');
            }
            if (!Schema::hasColumn('ca_client_compliance_requirements', 'recurrence_config')) {
                $table->json('recurrence_config')->nullable()->after('recurrence_frequency');
            }
            if (!Schema::hasColumn('ca_client_compliance_requirements', 'next_due_date')) {
                $table->date('next_due_date')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('ca_client_compliance_requirements', 'metadata_json')) {
                $table->json('metadata_json')->nullable()->after('remarks');
            }
        });

        // 3. ca_clients
        Schema::table('ca_clients', function (Blueprint $table) {
            // make ca_business_type_id nullable if it's not already
            $table->unsignedBigInteger('ca_business_type_id')->nullable()->change();
            
            if (!Schema::hasColumn('ca_clients', 'current_step')) {
                $table->integer('current_step')->default(1)->after('ca_business_type_id');
            }
            if (!Schema::hasColumn('ca_clients', 'onboarding_status')) {
                $table->string('onboarding_status')->default('in_progress')->after('status');
            }
            if (!Schema::hasColumn('ca_clients', 'onboarding_completed_at')) {
                $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_status');
            }
        });

        // 4. ca_business_types
        Schema::table('ca_business_types', function (Blueprint $table) {
            if (!Schema::hasColumn('ca_business_types', 'icon')) {
                $table->string('icon')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('ca_business_types', 'short_description')) {
                $table->string('short_description')->nullable()->after('description');
            }
            if (!Schema::hasColumn('ca_business_types', 'long_description')) {
                $table->text('long_description')->nullable()->after('short_description');
            }
            if (!Schema::hasColumn('ca_business_types', 'estimated_setup_time')) {
                $table->string('estimated_setup_time')->nullable()->after('long_description');
            }
            if (!Schema::hasColumn('ca_business_types', 'metadata_json')) {
                $table->json('metadata_json')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        // For brevity and safety in drafts, we can skip full down implementations 
        // or just drop the added columns.
    }
};
