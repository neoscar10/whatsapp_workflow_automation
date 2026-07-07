<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CAClientAutomationRule extends Model
{
    use HasFactory;

    protected $table = 'ca_client_automation_rules';

    public const TRIGGER_BEFORE_DUE = 'before_due';
    public const TRIGGER_ON_DUE     = 'on_due';
    public const TRIGGER_AFTER_DUE  = 'after_due';

    public const ALLOWED_TRIGGERS = [
        self::TRIGGER_BEFORE_DUE,
        self::TRIGGER_ON_DUE,
        self::TRIGGER_AFTER_DUE,
    ];

    protected $fillable = [
        'client_automation_id',
        'trigger_type',
        'offset_days',
        'send_time',
        'sequence',
        'is_enabled',
    ];

    protected $casts = [
        'offset_days' => 'integer',
        'sequence'    => 'integer',
        'is_enabled'  => 'boolean',
    ];

    public function clientAutomation(): BelongsTo
    {
        return $this->belongsTo(CAClientAutomation::class, 'client_automation_id');
    }

    /**
     * Returns a human-readable label for this rule.
     */
    public function getLabel(): string
    {
        if ($this->trigger_type === self::TRIGGER_ON_DUE) {
            return "On due date at {$this->send_time}";
        }

        $direction = $this->trigger_type === self::TRIGGER_BEFORE_DUE ? 'before' : 'after';
        return "{$this->offset_days} day(s) {$direction} due date at {$this->send_time}";
    }
}
