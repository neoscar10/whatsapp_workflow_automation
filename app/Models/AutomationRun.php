<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_flow_id',
        'company_id',
        'status',
        'current_node_id',
        'trigger_node_id',
        'trigger_context',
        'context',
        'step_count',
        'started_at',
        'completed_at',
        'metadata',
        'last_error',
    ];

    protected $casts = [
        'trigger_context' => 'array',
        'context' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'step_count' => 'integer',
    ];

    public function currentNode(): BelongsTo
    {
        return $this->belongsTo(AutomationNode::class, 'current_node_id');
    }

    public function flow(): BelongsTo
    {
        return $this->belongsTo(AutomationFlow::class, 'automation_flow_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
