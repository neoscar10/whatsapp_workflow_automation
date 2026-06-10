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
        Schema::create('ca_compliance_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ca_client_id')->constrained('ca_clients')->cascadeOnDelete();
            
            $table->unsignedBigInteger('ca_client_compliance_id')->nullable();
            $table->foreign('ca_client_compliance_id', 'fk_ca_timeline_comp_id')
                  ->references('id')->on('ca_client_compliances')->cascadeOnDelete();
                  
            $table->unsignedBigInteger('ca_client_compliance_requirement_id')->nullable();
            $table->foreign('ca_client_compliance_requirement_id', 'fk_ca_timeline_req_id')
                  ->references('id')->on('ca_client_compliance_requirements')->cascadeOnDelete();
                  
            $table->unsignedBigInteger('ca_document_id')->nullable();
            $table->foreign('ca_document_id', 'fk_ca_timeline_doc_id')
                  ->references('id')->on('ca_documents')->nullOnDelete();
            
            $table->string('event_key')->index(); // document_uploaded, deadline_due, etc.
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ca_compliance_timelines');
    }
};
