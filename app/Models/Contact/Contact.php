<?php

namespace App\Models\Contact;

use App\Models\Company;
use App\Models\User;
use App\Models\Chat\Conversation;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'has_opted_in' => 'boolean',
        'do_not_message' => 'boolean',
        'opted_in_at' => 'datetime',
        'opted_out_at' => 'datetime',
        'last_interaction_at' => 'datetime',
        'last_inbound_at' => 'datetime',
        'last_outbound_at' => 'datetime',
        'custom_fields' => 'array',
        'meta' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function whatsappPhoneNumber(): BelongsTo
    {
        return $this->belongsTo(WhatsAppPhoneNumber::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ContactTag::class, 'contact_contact_tag');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(ContactGroup::class, 'contact_contact_group');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Scope a query to only include contacts for a specific company.
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to search contacts.
     */
    public function scopeSearch($query, ?string $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('normalized_phone', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to only include messageable contacts.
     */
    public function scopeMessageable($query)
    {
        return $query->where(function ($q) {
            $q->where('status', '!=', 'blocked')
              ->orWhereNull('status');
        })
        ->where(function ($q) {
            $q->where('do_not_message', false)
              ->orWhereNull('do_not_message');
        });
    }

    /**
     * Determine if the contact can be messaged.
     */
    public function isMessageable(): bool
    {
        return ($this->status ?? 'active') !== 'blocked' 
            && !$this->do_not_message;
    }
}
