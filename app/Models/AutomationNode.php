<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationNode extends Model
{
    use HasFactory;
    protected $fillable = [
        'automation_flow_id',
        'type',
        'subtype',
        'label',
        'config',
        'position_x',
        'position_y',
    ];

    protected $casts = [
        'config' => 'array',
        'position_x' => 'integer',
        'position_y' => 'integer',
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(AutomationFlow::class, 'automation_flow_id');
    }

    public function outgoingConnections(): HasMany
    {
        return $this->hasMany(AutomationConnection::class, 'source_node_id');
    }

    public function incomingConnections(): HasMany
    {
        return $this->hasMany(AutomationConnection::class, 'target_node_id');
    }
}
