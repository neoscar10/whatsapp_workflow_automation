<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_client_compliance_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ca_client_compliance_id')->constrained('ca_client_compliances', 'id', 'fk_ccc_req_cc_id')->cascadeOnDelete();
            
            // Linking to master requirement, but allowing null if master is deleted
            $table->foreignId('ca_compliance_requirement_id')->nullable()->constrained('ca_compliance_requirements', 'id', 'fk_ccc_req_cr_id')->nullOnDelete();
            
            // Snapshotted details so history is preserved
            $table->string('name');
            $table->string('requirement_type');
            $table->string('input_type');
            $table->boolean('is_required')->default(true);
            
            // pending, submitted, under_review, approved, rejected, waived
            $table->string('status')->default('pending')->index();
            $table->boolean('is_completed')->default(false);
            
            $table->date('due_date')->nullable();
            
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_client_compliance_requirements');
    }
};
