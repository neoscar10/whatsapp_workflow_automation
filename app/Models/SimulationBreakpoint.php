<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulationBreakpoint extends Model
{
    protected $table = 'automation_simulation_breakpoints';

    protected $fillable = [
        'automation_flow_id',
        'node_id',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function automationFlow()
    {
        return $this->belongsTo(AutomationFlow::class);
    }
}
