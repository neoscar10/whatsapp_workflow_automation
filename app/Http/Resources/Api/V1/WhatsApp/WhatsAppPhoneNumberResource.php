<?php

namespace App\Http\Resources\Api\V1\WhatsApp;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsAppPhoneNumberResource extends JsonResource
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
            'company_id' => $this->company_id,
            'whatsapp_account_id' => $this->whatsapp_account_id,
            'display_name' => $this->display_name,
            'phone_number_id' => $this->phone_number_id,
            'phone_number' => $this->phone_number,
            'status' => $this->status,
            'verified_name' => $this->verified_name,
            'quality_rating' => $this->quality_rating,
            'code_verification_status' => $this->code_verification_status,
            'synced_at' => $this->synced_at?->toIso8601String(),
            'last_sync_error' => $this->last_sync_error,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
