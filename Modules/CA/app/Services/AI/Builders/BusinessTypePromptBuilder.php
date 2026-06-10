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
                    "frequency": "one_time", // options: monthly, quarterly, half_yearly, annually, one_time
                    "due_month": null, // integer 1-12 (null if not applicable)
                    "due_day": null, // integer 1-31 (null if not applicable)
                    "requirements": [
                        {
                            "name": "PAN Card",
                            "description": "Company PAN Card",
                            "requirement_type": "document", // document, text, date, number, boolean
                            "input_type": "file", // file, image, pdf, text, textarea, date, select, checkbox, multi_file
                            "is_required": true,
                            "is_recurring": false,
                            "required_when": "Required Now", // "Required Now" or "Required Later"
                            "document_type": {
                                "name": "PAN Card",
                                "category": "Identity",
                                "allowed_extensions": ["pdf", "jpg", "png"],
                                "allowed_mime_types": ["application/pdf", "image/jpeg", "image/png"],
                                "preview_type": "image"
                            },
                            "validation_notes": "Must be clear and legible."
                        }
                    ],
                    "deadlines": [
                        {
                            "deadline_name": "GST Registration Due",
                            "deadline_type": "Statutory",
                            "due_date_rule": "Within 30 days of crossing threshold"
                        }
                    ]
                }
            ]
        }
    ]
}
Do not include any markdown formatting, only raw valid JSON.
PROMPT;
    }
}
