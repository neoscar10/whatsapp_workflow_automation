<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppTemplateButton extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_template_buttons';

    protected $fillable = [
        'whatsapp_template_id',
        'type',
        'text',
        'url',
        'phone_number',
        'example_value',
        'sort_order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'whatsapp_template_id');
    }
}
