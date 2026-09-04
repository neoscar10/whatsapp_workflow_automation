<?php

namespace App\Http\Resources\Api\V1\Campaign;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignRecipientResource extends JsonResource
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
            'campaign_id' => $this->campaign_id,
            'contact_id' => $this->contact_id,
            'conversation_id' => $this->conversation_id,
            'conversation_message_id' => $this->conversation_message_id,
            'phone' => $this->phone,
            'normalized_phone' => $this->normalized_phone,
            'name' => $this->name,
            'source' => $this->source,
            'status' => $this->status,
            'skip_reason' => $this->skip_reason,
            'personalization_data' => $this->personalization_data,
            'provider_message_id' => $this->provider_message_id,
            'meta_error_code' => $this->meta_error_code,
            'meta_error_message' => $this->meta_error_message,
            'error_code' => $this->meta_error_code,
            'error_message' => $this->meta_error_message ?: $this->skip_reason,
            'error' => $this->status === 'failed' ? [
                'code' => $this->meta_error_code,
                'message' => $this->meta_error_message,
            ] : null,
            'attempts' => $this->attempts,
            'last_attempted_at' => $this->last_attempted_at?->toDateTimeString(),
            'sent_at' => $this->sent_at?->toDateTimeString(),
            'delivered_at' => $this->delivered_at?->toDateTimeString(),
            'read_at' => $this->read_at?->toDateTimeString(),
            'failed_at' => $this->failed_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
