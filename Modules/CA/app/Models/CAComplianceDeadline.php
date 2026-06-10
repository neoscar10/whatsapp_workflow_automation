<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAComplianceDeadline extends Model
{
    use HasFactory;

    protected $table = 'ca_compliance_deadlines';

    protected $fillable = [
        'ca_compliance_id',
        'frequency',
        'due_day',
        'due_month',
        'reminder_window',
        'description',
        'status',
    ];

    public function compliance(): BelongsTo
    {
        return $this->belongsTo(CACompliance::class, 'ca_compliance_id');
    }
}
