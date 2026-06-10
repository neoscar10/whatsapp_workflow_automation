<?php

namespace Modules\CA\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CADocumentType extends Model
{
    use HasFactory;

    protected $table = 'ca_document_types';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'allowed_extensions',
        'allowed_mime_types',
        'max_file_size',
        'preview_type',
        'status',
    ];

    protected $casts = [
        'allowed_extensions' => 'array',
        'allowed_mime_types' => 'array',
    ];
}
