<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'primary_email',
        'website_url',
        'description',
        'logo_path',
        'status',
        'trial_starts_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function users(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(User::class);
    }

    public function whatsappAccount(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\WhatsApp\WhatsAppAccount::class);
    }

    public function whatsappPhoneNumbers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\WhatsApp\WhatsAppPhoneNumber::class);
    }

    public function whatsappTemplates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\WhatsApp\WhatsAppTemplate::class);
    }
}
