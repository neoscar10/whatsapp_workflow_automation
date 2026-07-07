<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CANotification extends Model
{
    use HasFactory;

    protected $table = 'ca_notifications';

    protected $fillable = [
        'company_id',
        'ca_client_id',
        'contact_id',
        'type',
        'title',
        'message',
        'status',
        'metadata_json',
        'read_at',
        'action_url',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'read_at'       => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(CAClient::class, 'ca_client_id');
    }
}
