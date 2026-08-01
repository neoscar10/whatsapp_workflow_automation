<?php

namespace App\Http\Resources\Api\V1\Webhook;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyWebhookDeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_webhook_id' => $this->company_webhook_id,
            'event_type' => $this->event_type,
            'payload' => $this->payload,
            'status_code' => $this->status_code,
            'response_body' => $this->response_body,
            'error_message' => $this->error_message,
            'attempt' => $this->attempt,
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
