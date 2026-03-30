<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SimulationStep extends Model
{
    protected $table = 'automation_simulation_steps';

    protected $fillable = [
        'simulation_session_id',
        'node_id',
        'node_type',
        'node_subtype',
        'status',
        'input_snapshot',
        'output_snapshot',
        'log_message',
        'order_index',
    ];

    protected $casts = [
        'input_snapshot' => 'array',
        'output_snapshot' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(SimulationSession::class, 'simulation_session_id');
    }
}
