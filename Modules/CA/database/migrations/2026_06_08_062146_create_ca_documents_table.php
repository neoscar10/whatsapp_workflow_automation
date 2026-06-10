<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ca_documents', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ca_client_id')->nullable()->constrained('ca_clients')->cascadeOnDelete();
            $table->foreignId('ca_client_compliance_id')->nullable()->constrained('ca_client_compliances', 'id', 'fk_cadoc_cc_id')->nullOnDelete();
            $table->foreignId('ca_client_compliance_requirement_id')->nullable()->constrained('ca_client_compliance_requirements', 'id', 'fk_cadoc_ccr_id')->nullOnDelete();
            $table->foreignId('ca_document_type_id')->nullable()->constrained('ca_document_types')->nullOnDelete();
            
            $table->string('document_name');
            $table->string('document_type')->nullable(); // general classification if type_id is null
            
            $table->string('mime_type')->nullable();
            $table->string('extension')->nullable();
            $table->string('storage_disk')->default('local');
            $table->string('storage_path');
            $table->string('original_filename');
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->integer('version')->default(1);
            
            // uploaded, under_review, approved, rejected, expired
            $table->string('status')->default('uploaded')->index();
            
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->json('metadata_json')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ca_documents');
    }
};
