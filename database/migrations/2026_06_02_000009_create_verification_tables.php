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
        Schema::create('company_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('status')->default('not_started'); // not_started, in_progress, under_review, partially_approved, verified, rejected, expired, suspended
            $table->integer('progress_percentage')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });

        Schema::create('company_verification_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_verification_id');
            $table->uuid('document_type_id');
            $table->string('status')->default('not_submitted'); // not_submitted, pending_review, approved, rejected, resubmission_required
            $table->timestamps();

            $table->foreign('company_verification_id')
                ->references('id')
                ->on('company_verifications')
                ->cascadeOnDelete();

            $table->foreign('document_type_id')
                ->references('id')
                ->on('document_types')
                ->cascadeOnDelete();

            $table->index(['company_verification_id', 'status'], 'comp_doc_status_idx');
        });

        Schema::create('company_verification_document_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_verification_document_id');
            $table->integer('version_number')->default(1);
            $table->string('file_path');
            $table->string('file_name');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('status')->default('pending_review'); // pending_review, approved, rejected
            $table->string('rejection_reason')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->foreign('company_verification_document_id', 'fk_comp_ver_doc_versions')
                ->references('id')
                ->on('company_verification_documents')
                ->cascadeOnDelete();

            $table->index(['company_verification_document_id', 'status'], 'comp_doc_ver_status_idx');
        });

        Schema::create('company_verification_timelines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_verification_id');
            $table->string('event_type'); // upload, approve_doc, reject_doc, status_change, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('company_verification_id')
                ->references('id')
                ->on('company_verifications')
                ->cascadeOnDelete();

            $table->index(['company_verification_id', 'created_at'], 'comp_ver_time_idx');
        });

        Schema::create('verification_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_audit_logs');
        Schema::dropIfExists('company_verification_timelines');
        Schema::dropIfExists('company_verification_document_versions');
        Schema::dropIfExists('company_verification_documents');
        Schema::dropIfExists('company_verifications');
    }
};
