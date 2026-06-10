<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_client_compliance_deadlines', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('ca_client_compliance_id')->constrained('ca_client_compliances', 'id', 'fk_cc_deadlines_cc_id')->cascadeOnDelete();
            $table->foreignId('ca_client_compliance_requirement_id')->nullable()->constrained('ca_client_compliance_requirements', 'id', 'fk_cc_deadlines_ccr_id')->cascadeOnDelete();
            
            $table->string('deadline_name'); // e.g. "Bank Statement Due", "Quarter End Filing"
            $table->string('deadline_type')->nullable(); // general categorization
            
            $table->date('due_date');
            
            // pending, completed, missed, waived
            $table->string('status')->default('pending')->index();
            $table->timestamp('completed_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_client_compliance_deadlines');
    }
};
