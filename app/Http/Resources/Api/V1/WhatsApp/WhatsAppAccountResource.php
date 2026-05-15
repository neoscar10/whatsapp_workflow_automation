<?php

namespace App\Http\Resources\Api\V1\WhatsApp;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsAppAccountResource extends JsonResource
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
            'waba_id' => $this->waba_id,
            'business_id' => $this->business_id,
            'connection_status' => $this->connection_status,
            'webhook_status' => $this->webhook_status,
            'webhook_callback_url' => $this->webhook_callback_url,
            'webhook_verify_token' => $this->webhook_verify_token,
            'webhook_verified_at' => $this->webhook_verified_at?->toIso8601String(),
            'webhook_last_checked_at' => $this->webhook_last_checked_at?->toIso8601String(),
            'webhook_last_error' => $this->webhook_last_error,
            'webhook_subscription_status' => $this->webhook_subscription_status,
            'webhook_subscribed_at' => $this->webhook_subscribed_at?->toIso8601String(),
            'connected_at' => $this->connected_at?->toIso8601String(),
            'last_verified_at' => $this->last_verified_at?->toIso8601String(),
            'last_synced_at' => $this->last_synced_at?->toIso8601String(),
            'last_sync_error' => $this->last_sync_error,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
