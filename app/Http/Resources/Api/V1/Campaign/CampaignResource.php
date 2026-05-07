<?php

namespace App\Http\Resources\Api\V1\Campaign;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
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
            'recipient_count' => $this->recipient_count,
            'eligible_recipient_count' => $this->eligible_recipient_count,
            'skipped_recipient_count' => $this->skipped_recipient_count,
            'pending_count' => $this->pending_count,
            'sent_count' => $this->sent_count,
            'delivered_count' => $this->delivered_count,
            'read_count' => $this->read_count,
            'failed_count' => $this->failed_count,
            'scheduled_at' => $this->scheduled_at?->toDateTimeString(),
            'started_at' => $this->started_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),
            'paused_at' => $this->paused_at?->toDateTimeString(),
            'cancelled_at' => $this->cancelled_at?->toDateTimeString(),
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
