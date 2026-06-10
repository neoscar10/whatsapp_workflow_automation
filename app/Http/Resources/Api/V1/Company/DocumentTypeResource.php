<?php

namespace App\Http\Resources\Api\V1\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_required' => (bool)$this->is_required,
            'requires_expiry_date' => (bool)$this->requires_expiry_date,
            'max_size_mb' => $this->max_size_mb,
            'accepted_formats' => $this->accepted_formats,
            'sort_order' => $this->sort_order,
        ];
    }
}
