<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Company;
use App\Models\User;

class CADocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ca_documents';

    protected $fillable = [
        'company_id',
        'ca_client_id',
        'ca_client_compliance_id',
        'ca_client_compliance_requirement_id',
        'ca_document_type_id',
        'document_name',
        'document_type',
        'mime_type',
        'extension',
        'storage_disk',
        'storage_path',
        'original_filename',
        'file_size',
        'version',
        'status',
        'uploaded_by',
        'approved_by',
        'approved_at',
        'expires_at',
        'metadata_json',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata_json' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(CAClient::class, 'ca_client_id');
    }

    public function clientCompliance(): BelongsTo
    {
        return $this->belongsTo(CAClientCompliance::class, 'ca_client_compliance_id');
    }

    public function clientComplianceRequirement(): BelongsTo
    {
        return $this->belongsTo(CAClientComplianceRequirement::class, 'ca_client_compliance_requirement_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->clientComplianceRequirement();
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(CADocumentType::class, 'ca_document_type_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
