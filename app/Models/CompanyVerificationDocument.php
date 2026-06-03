<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CompanyVerificationDocument extends Model
{
    use HasUuids;

    protected $table = 'company_verification_documents';

    protected $fillable = [
        'company_verification_id',
        'document_type_id',
        'status',
    ];

    public function verification(): BelongsTo
    {
        return $this->belongsTo(CompanyVerification::class, 'company_verification_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(CompanyVerificationDocumentVersion::class, 'company_verification_document_id')->orderBy('version_number', 'desc');
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(CompanyVerificationDocumentVersion::class, 'company_verification_document_id')->latestOfMany();
    }
}
