<?php

namespace App\Http\Resources\Api\V1\Company;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyVerificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approvedCount = 0;
        $pendingCount = 0;
        $rejectedCount = 0;

        if ($this->relationLoaded('documents')) {
            foreach ($this->documents as $doc) {
                if ($doc->relationLoaded('latestVersion') && $doc->latestVersion) {
                    $status = $doc->latestVersion->status;
                    if ($status === 'approved') {
                        $approvedCount++;
                    } elseif ($status === 'rejected') {
                        $rejectedCount++;
                    } else {
                        $pendingCount++;
                    }
                }
            }
        }

        return [
            'id' => $this->id,
            'status' => $this->status,
            'notes' => $this->notes,
            'approved_documents_count' => $approvedCount,
            'pending_documents_count' => $pendingCount,
            'rejected_documents_count' => $rejectedCount,
            'documents' => VerificationDocumentResource::collection($this->whenLoaded('documents')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
