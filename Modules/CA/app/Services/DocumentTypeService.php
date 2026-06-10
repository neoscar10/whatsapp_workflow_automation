<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CADocumentType;
use Illuminate\Support\Str;

class DocumentTypeService
{
    /**
     * Get or create a Document Type from AI metadata
     */
    public function getOrCreateFromMetadata(array $metadata): CADocumentType
    {
        $name = $metadata['name'] ?? 'General Document';
        $slug = Str::slug($name);

        return CADocumentType::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'category' => $metadata['category'] ?? 'General',
                'allowed_extensions' => $metadata['allowed_extensions'] ?? ['pdf', 'jpg', 'png', 'jpeg'],
                'allowed_mime_types' => $metadata['allowed_mime_types'] ?? ['application/pdf', 'image/jpeg', 'image/png'],
                'preview_type' => $metadata['preview_type'] ?? 'document',
            ]
        );
    }
}
