<?php

namespace App\Http\Resources\Api\V1\WhatsApp;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsAppTemplateResource extends JsonResource
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
            'whatsapp_account_id' => $this->whatsapp_account_id,
            'name' => $this->remote_template_name,
            'display_title' => $this->display_title,
            'language' => $this->language_code,
            'category' => $this->category,
            'status' => $this->status,
            'quality_rating' => $this->quality_rating,
            'rejection_reason' => $this->rejection_reason,
            'header_type' => $this->header_type,
            'header_text' => $this->header_text,
            'body_text' => $this->body_text,
            'footer_text' => $this->footer_text,
            'button_count' => $this->button_count,
            'buttons' => $this->buttons->map(fn($btn) => [
                'type' => $btn->type,
                'text' => $btn->text,
                'url' => $btn->url,
                'phone_number' => $btn->phone_number,
            ]),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
