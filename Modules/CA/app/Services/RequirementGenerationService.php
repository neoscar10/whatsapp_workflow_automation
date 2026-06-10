<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CACompliance;
use Modules\CA\Models\CAComplianceRequirement;
use Modules\CA\Models\CAComplianceDeadline;
use Illuminate\Support\Str;

class RequirementGenerationService
{
    /**
     * Parse AI JSON data for a specific compliance and create master requirements
     */
    public function generateMasterRequirements(CACompliance $compliance, array $aiData): void
    {
        // Ensure requirements array exists in the AI payload
        if (!isset($aiData['requirements']) || !is_array($aiData['requirements'])) {
            return;
        }

        $sortOrder = 0;
        foreach ($aiData['requirements'] as $reqData) {
            $sortOrder++;
            
            CAComplianceRequirement::updateOrCreate(
                [
                    'ca_compliance_id' => $compliance->id,
                    'slug' => Str::slug($reqData['name']),
                ],
                [
                    'name' => $reqData['name'],
                    'description' => $reqData['description'] ?? null,
                    'requirement_type' => $reqData['requirement_type'] ?? 'document',
                    'input_type' => $reqData['input_type'] ?? 'file',
                    'is_required' => $reqData['is_required'] ?? true,
                    'is_recurring' => $reqData['is_recurring'] ?? false,
                    'sort_order' => $sortOrder,
                ]
            );
        }

        // Also generate Master Deadlines if they don't exist
        if (isset($aiData['deadlines']) && is_array($aiData['deadlines'])) {
            foreach ($aiData['deadlines'] as $dlData) {
                CAComplianceDeadline::updateOrCreate(
                    [
                        'ca_compliance_id' => $compliance->id,
                        'name' => $dlData['deadline_name'],
                    ],
                    [
                        'deadline_rule' => $dlData['due_date_rule'] ?? null,
                    ]
                );
            }
        }
    }
}
