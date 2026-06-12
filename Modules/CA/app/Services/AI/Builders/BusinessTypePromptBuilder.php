<?php

namespace Modules\CA\Services\AI\Builders;

class BusinessTypePromptBuilder
{
    public function buildComplianceKnowledgePrompt(string $businessTypeName): string
    {
        return <<<PROMPT
I need a comprehensive list of all regulatory and statutory compliances applicable to a "{$businessTypeName}" in India.

Return the response STRICTLY as a JSON object with the following structure:
{
    "business_type": "{$businessTypeName}",
    "service_categories": [
        {
            "name": "Category Name (e.g., Direct Tax, ROC, GST)",
            "description": "Short description of this category",
            "compliances": [
                {
                    "name": "Compliance Name (e.g., GST Registration)",
                    "description": "What is this compliance about?",
                    "is_recurring": false,
                    "requirements": [
                        {
                            "name": "PAN Card",
                            "description": "Company PAN Card",
                            "requirement_type": "document", // document, text, date, number, boolean
                            "input_type": "file", // file, image, pdf, text, textarea, date, select, checkbox, multi_file
                            "is_required": true,
                            "is_recurring": false, // Must be true or false
                            "required_stage": "onboarding", // "onboarding", "post_onboarding", or "both" (default to onboarding if is_recurring=false, post_onboarding if is_recurring=true)
                            "document_type": "pan_card", // standard document type key if applicable
                            "validation_notes": "Must be clear and legible."
                        }
                    ],
                    "deadlines": []
                }
            ]
        }
    ]
}
Do not include any markdown formatting, only raw valid JSON.
Note: You must NOT specify frequency, due_month, or due_day for compliances. CA firms will configure recurrence frequencies manually. AI should only identify if a requirement is recurring or not.
PROMPT;
    }
}
