<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyVerificationDocumentVersion extends Model
{
    use HasUuids;

    protected $table = 'company_verification_document_versions';

    protected $fillable = [
        'company_verification_document_id',
        'version_number',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'status',
        'rejection_reason',
        'reviewer_notes',
        'uploaded_by',
        'issue_date',
        'expiry_date',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(CompanyVerificationDocument::class, 'company_verification_document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDownloadUrl(): string
    {
        return route('company.verification.file', ['versionId' => $this->id]);
    }
}
