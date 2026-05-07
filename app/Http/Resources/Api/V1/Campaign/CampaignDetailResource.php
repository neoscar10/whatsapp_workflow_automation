<?php

namespace App\Http\Resources\Api\V1\Campaign;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignDetailResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'audience_type' => $this->audience_type,
            'whatsapp_phone_number_id' => $this->whatsapp_phone_number_id,
            'whatsapp_template_id' => $this->whatsapp_template_id,
            'message_body' => $this->message_body,
            'template_info' => $this->whatsapp_template_id ? [
                'name' => $this->template_name,
                'language' => $this->template_language,
                'components' => $this->template_components,
                'variable_mapping' => $this->template_variable_mapping,
                'default_values' => $this->default_variable_values,
            ] : null,
            'audience_filters' => $this->audience_filters,
            'personalization_config' => $this->personalization_config,
            'recipient_stats' => [
                'total' => $this->recipient_count,
                'eligible' => $this->eligible_recipient_count,
                'skipped' => $this->skipped_recipient_count,
                'pending' => $this->pending_count,
                'sent' => $this->sent_count,
                'delivered' => $this->delivered_count,
                'read' => $this->read_count,
                'failed' => $this->failed_count,
            ],
            'timeline' => [
                'scheduled_at' => $this->scheduled_at?->toDateTimeString(),
                'started_at' => $this->started_at?->toDateTimeString(),
                'completed_at' => $this->completed_at?->toDateTimeString(),
                'paused_at' => $this->paused_at?->toDateTimeString(),
                'cancelled_at' => $this->cancelled_at?->toDateTimeString(),
            ],
            'meta' => $this->meta,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'permissions' => [
                'can_edit' => $this->canEdit(),
                'can_send' => $this->canSend(),
                'can_pause' => $this->canPause(),
                'can_cancel' => $this->canCancel(),
                'can_duplicate' => $this->canDuplicate(),
            ]
        ];
    }
}
