<?php

namespace App\Models\WhatsApp;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppTemplate extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'company_id',
        'whatsapp_account_id',
        'remote_template_id',
        'remote_template_name',
        'display_title',
        'category',
        'language_code',
        'status',
        'quality_rating',
        'namespace',
        'rejection_reason',
        'header_type',
        'header_text',
        'body_text',
        'footer_text',
        'button_count',
        'example_payload',
        'meta_payload',
        'last_synced_at',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'archived_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'example_payload' => 'array',
        'meta_payload' => 'array',
        'last_synced_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(WhatsAppAccount::class, 'whatsapp_account_id');
    }

    public function buttons(): HasMany
    {
        return $this->hasMany(WhatsAppTemplateButton::class, 'whatsapp_template_id')->orderBy('sort_order');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }
}
