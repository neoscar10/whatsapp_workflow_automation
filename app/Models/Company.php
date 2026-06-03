<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public static array $countries = [
        'IN' => 'India',
        'NG' => 'Nigeria',
        'GB' => 'United Kingdom',
        'US' => 'United States',
        'CA' => 'Canada',
        'AU' => 'Australia',
        'DE' => 'Germany',
        'FR' => 'France',
        'AE' => 'United Arab Emirates',
        'ZA' => 'South Africa',
        'KE' => 'Kenya',
        'GH' => 'Ghana',
        'SG' => 'Singapore',
        'BR' => 'Brazil',
        'MX' => 'Mexico',
        'ES' => 'Spain',
        'IT' => 'Italy',
        'NL' => 'Netherlands',
        'IE' => 'Ireland',
        'SA' => 'Saudi Arabia',
        'EG' => 'Egypt',
        'ID' => 'Indonesia',
        'MY' => 'Malaysia',
        'PH' => 'Philippines',
        'JP' => 'Japan',
        'KR' => 'South Korea',
    ];

    protected $fillable = [
        'name',
        'slug',
        'primary_email',
        'website_url',
        'description',
        'logo_path',
        'status',
        'country',
        'demo_credits',
        'demo_ends_at',
        'demo_whatsapp_phone_number_id',
        'trial_starts_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'demo_credits' => 'decimal:4',
        'demo_ends_at' => 'datetime',
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

    public function demoPhoneNumber(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\WhatsApp\WhatsAppPhoneNumber::class, 'demo_whatsapp_phone_number_id');
    }

    public function checkDemoExpiry()
    {
        if ($this->status === 'demo' && $this->demo_ends_at && $this->demo_ends_at->isPast()) {
            $this->update([
                'status' => 'active',
                'demo_ends_at' => null,
                'demo_whatsapp_phone_number_id' => null,
            ]);
        }
    }

    public function whatsappTemplates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\WhatsApp\WhatsAppTemplate::class);
    }

    public function isVerified(): bool
    {
        $verification = CompanyVerification::where('company_id', $this->id)->first();
        return $verification && $verification->status === 'verified';
    }
}
