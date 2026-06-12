<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CABusinessType;
use Modules\CA\Models\CAServiceCategory;
use Modules\CA\Models\CACompliance;
use Modules\CA\Models\CAComplianceRequirement;
use Illuminate\Support\Str;

class ComplianceKnowledgePersistenceService
{
    /**
     * Parse AI JSON and persist compliance knowledge to the database.
     * 
     * @param CABusinessType $businessType
     * @param array $intelligence
     * @return int Total generated/synced compliances
     */
    public function persistKnowledge(CABusinessType $businessType, array $intelligence): int
    {
        $totalItems = 0;

        if (empty($intelligence) || !isset($intelligence['service_categories'])) {
            return $totalItems;
        }

        foreach ($intelligence['service_categories'] as $catData) {
            $category = CAServiceCategory::firstOrCreate(
                ['slug' => Str::slug($catData['name'])],
                [
                    'name' => $catData['name'],
                    'description' => $catData['description'] ?? null,
                    'sort_order' => 0
                ]
            );

            foreach ($catData['compliances'] as $compData) {
                $compliance = CACompliance::updateOrCreate(
                    ['slug' => Str::slug($compData['name'])],
                    [
                        'ca_service_category_id' => $category->id,
                        'name' => $compData['name'],
                        'description' => $compData['description'] ?? null,
                        'is_recurring' => $compData['is_recurring'] ?? false,
                    ]
                );
                
                // Save requirements
                if (isset($compData['requirements']) && is_array($compData['requirements'])) {
                    foreach ($compData['requirements'] as $reqData) {
                        $isRecurring = $reqData['is_recurring'] ?? false;
                        $defaultStage = $isRecurring ? 'post_onboarding' : 'onboarding';

                        CAComplianceRequirement::updateOrCreate(
                            [
                                'ca_compliance_id' => $compliance->id,
                                'slug' => Str::slug($reqData['name'])
                            ],
                            [
                                'name' => $reqData['name'],
                                'description' => $reqData['description'] ?? null,
                                'requirement_type' => $reqData['requirement_type'] ?? 'document',
                                'input_type' => $reqData['input_type'] ?? 'file',
                                'is_required' => $reqData['is_required'] ?? true,
                                'is_recurring' => $isRecurring,
                                'required_stage' => $reqData['required_stage'] ?? $defaultStage,
                                'document_type' => $reqData['document_type'] ?? null,
                                'validation_notes' => $reqData['validation_notes'] ?? null,
                            ]
                        );
                    }
                }

                // AI no longer manages frequencies or deadlines here.
                // We sync the compliance to the business type.
                $businessType->compliances()->syncWithoutDetaching([$compliance->id]);
                $totalItems++;
            }
        }

        return $totalItems;
    }
}
