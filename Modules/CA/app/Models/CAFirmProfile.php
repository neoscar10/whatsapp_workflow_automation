<?php

namespace Modules\CA\Models;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAFirmProfile extends Model
{
    use HasFactory;

    protected $table = 'ca_firm_profiles';

    protected $fillable = [
        'company_id',
        'icai_registration_number',
        'firm_type',
        'firm_email',
        'firm_phone',
        'firm_address',
        'firm_city',
        'firm_state',
        'firm_country',
        'settings_json',
        'status',
    ];

    protected $casts = [
        'settings_json' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
