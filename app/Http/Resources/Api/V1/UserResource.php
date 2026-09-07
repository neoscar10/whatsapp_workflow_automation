<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'company_id' => $this->company_id,
            'is_company_owner' => $this->is_company_owner,
            'company' => [
                'id' => $this->company->id ?? null,
                'name' => $this->company->name ?? null,
                'status' => $this->company->status ?? null,
                'is_demo' => ($this->company?->status === 'demo'),
                'demo_credits' => $this->company?->demo_credits ? (float) $this->company->demo_credits : 0.0,
                'demo_ends_at' => $this->company?->demo_ends_at?->toIso8601String(),
                'country' => $this->company?->country ?? 'IN',
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
