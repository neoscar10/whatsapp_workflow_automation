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
            'company_id' => $this->company_id,
            'business_type' => $this->business_type,
            'legal_name' => $this->legal_name,
            'display_name' => $this->display_name,
            'category' => $this->category,
            'website' => $this->website,
            'address' => $this->address,
            'signatory_name' => $this->signatory_name,
            'signatory_designation' => $this->signatory_designation,
            'business_email' => $this->business_email,
            'business_phone' => $this->business_phone,
            'wa_phone' => $this->wa_phone,
            'status' => $this->status,
            'progress_percentage' => (int) $this->progress_percentage,
            'submitted_at' => $this->submitted_at,
            'last_activity_at' => $this->last_activity_at,
            'approved_documents_count' => $approvedCount,
            'pending_documents_count' => $pendingCount,
            'rejected_documents_count' => $rejectedCount,
            'documents' => VerificationDocumentResource::collection($this->whenLoaded('documents')),
            'timeline' => VerificationTimelineResource::collection($this->whenLoaded('timeline')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
