<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Company;
use App\Models\Contact\Contact;

class CAClient extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ca_clients';

    protected $fillable = [
        'company_id',
        'contact_id',
        'ca_business_type_id',
        'client_name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'status',
        'notes',
        'created_by',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(CABusinessType::class, 'ca_business_type_id');
    }

    public function clientCompliances(): HasMany
    {
        return $this->hasMany(CAClientCompliance::class, 'ca_client_id');
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(CAComplianceTimeline::class, 'ca_client_id');
    }
}
