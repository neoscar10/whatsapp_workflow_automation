<?php

namespace App\Models\WhatsApp;

use Illuminate\Database\Eloquent\Model;

class WhatsAppSimulatedMedia extends Model
{
    protected $table = 'whatsapp_simulated_media';

    protected $fillable = [
        'simulated_media_id',
        'company_id',
        'contact_id',
        'uploaded_by',
        'original_filename',
        'mime_type',
        'extension',
        'file_size',
        'storage_disk',
        'storage_path',
    ];
}
