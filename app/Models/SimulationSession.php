<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulationSession extends Model
{
    protected $table = 'automation_simulation_sessions';

    protected $fillable = [
        'automation_flow_id',
        'company_id',
        'status',
        'current_node_id',
        'context',
        'initial_payload',
        'started_at',
        'completed_at',
        'meta',
    ];

    protected $casts = [
        'context' => 'array',
        'initial_payload' => 'array',
        'meta' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function automationFlow()
    {
        return $this->belongsTo(AutomationFlow::class);
    }

    public function steps()
    {
        return $this->hasMany(SimulationStep::class, 'simulation_session_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
