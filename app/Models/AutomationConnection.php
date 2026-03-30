<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationConnection extends Model
{
    protected $fillable = [
        'automation_flow_id',
        'source_node_id',
        'target_node_id',
        'source_handle',
        'target_handle',
        'condition_key',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(AutomationFlow::class, 'automation_flow_id');
    }

    public function sourceNode(): BelongsTo
    {
        return $this->belongsTo(AutomationNode::class, 'source_node_id');
    }

    public function targetNode(): BelongsTo
    {
        return $this->belongsTo(AutomationNode::class, 'target_node_id');
    }
}
