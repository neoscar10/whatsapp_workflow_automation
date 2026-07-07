<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAAutomationLibrary;
use Modules\CA\Models\CAAIAutomationTemplate;
use Modules\CA\Services\AI\Managers\AIManager;
use Illuminate\Support\Facades\Log;
use Exception;

class AutomationTemplateLibraryService
{
    public const PROMPT_VERSION = '1.0';
    public const CACHE_VERSION  = '1.0';

    public function __construct(
        private AIManager $aiManager
    ) {}

    /**
     * Get or generate an AI template for a specific library entry.
     *
     * Strategy:
     *  1. Check DB (ca_ai_automation_templates) - return if found (zero AI cost).
     *  2. If not found: call AI, save to DB, return.
     */
    public function getOrGenerateTemplate(
        CAAutomationLibrary $library,
        string $language = 'en',
        string $tone = 'professional',
        bool $forceRegenerate = false,
        bool $isOverdue = false
    ): array {
        // Use a unique tone cache key for overdue templates to avoid clashing with regular templates (e.g. 'urgent' tone)
        $cacheTone = $isOverdue ? $tone . '_overdue' : $tone;

        // 1. Check DB first
        if (!$forceRegenerate) {
            $cached = CAAIAutomationTemplate::forLibrary($library->id, $language, $cacheTone)->first();
            if ($cached) {
                return [
                    'message_title' => $cached->message_title,
                    'message_body'  => $cached->message_body,
                    'from_cache'    => true,
                ];
            }
        }

        // 2. Generate via AI
        try {
            $provider      = $this->aiManager->provider();
            $systemPrompt  = $this->buildSystemPrompt($isOverdue);
            $userPrompt    = $this->buildUserPrompt($library, $language, $tone, $isOverdue);

            $response = $provider->generateStructuredResponse($systemPrompt, $userPrompt);

            $title = $response['message_title'] ?? "{$library->name} " . ($isOverdue ? 'Overdue Notice' : 'Reminder');
            $body  = $response['message_body']  ?? $this->getFallbackBody($library, $tone, $isOverdue);

            // 3. Save/Update in DB for future reuse
            CAAIAutomationTemplate::updateOrCreate(
                [
                    'automation_library_id' => $library->id,
                    'frequency'             => $library->frequency,
                    'language'              => $language,
                    'tone'                  => $cacheTone,
                ],
                [
                    'message_title'         => $title,
                    'message_body'          => $body,
                    'prompt_version'        => self::PROMPT_VERSION,
                    'ai_provider'           => $provider->getName(),
                    'ai_model'              => $provider->getModel(),
                    'cache_version'         => self::CACHE_VERSION,
                ]
            );

            return [
                'message_title' => $title,
                'message_body'  => $body,
                'from_cache'    => false,
            ];

        } catch (Exception $e) {
            Log::warning("AutomationTemplateLibraryService AI failed for library #{$library->id}: " . $e->getMessage());

            // Return a sensible fallback so onboarding never blocks
            return [
                'message_title' => "{$library->name} " . ($isOverdue ? 'Overdue Notice' : 'Reminder'),
                'message_body'  => $this->getFallbackBody($library, $tone, $isOverdue),
                'from_cache'    => false,
            ];
        }
    }

    private function buildSystemPrompt(bool $isOverdue = false): string
    {
        if ($isOverdue) {
            return <<<EOT
You are a professional business communication expert specializing in Indian Chartered Accountancy firms.
Your task is to generate WhatsApp OVERDUE ESCALATION messages that are:
- Firm, urgent, and clearly communicating the document is ALREADY OVERDUE and past its deadline
- Must explicitly state the document has NOT been submitted yet and the deadline has already passed
- Use words like "overdue", "past due", "deadline missed", "action required immediately"
- Professional but urgent in tone — never use "reminder" framing; this is an escalation
- Concise (under 250 words)
- Include placeholders in {{double_braces}} for: client_name, firm_name, document_name, due_date, days_remaining
Return JSON with keys: message_title, message_body
EOT;
        }

        return <<<EOT
You are a professional business communication expert specializing in Indian Chartered Accountancy firms.
Your task is to generate WhatsApp reminder messages that are:
- Professional yet friendly
- Concise (under 250 words)
- Action-oriented
- Include placeholders in {{double_braces}} for: client_name, firm_name, document_name, due_date, days_remaining
Return JSON with keys: message_title, message_body
EOT;
    }

    private function buildUserPrompt(CAAutomationLibrary $library, string $language, string $tone, bool $isOverdue = false): string
    {
        if ($isOverdue) {
            return <<<EOT
Generate a WhatsApp OVERDUE ESCALATION message for the following context:

Automation Type: {$library->name}
Frequency: {$library->frequency}
Language: {$language}
Description: {$library->description}

IMPORTANT: The document has ALREADY MISSED its deadline. The client has NOT submitted their {$library->frequency} compliance document yet.
The message must:
- Open by clearly stating the document submission is OVERDUE / past due
- NOT frame this as a "reminder" — it is an escalation notice
- Explicitly reference that the deadline ({{due_date}}) has already passed
- State how many days overdue it is ({{days_remaining}})
- Request IMMEDIATE submission
- Mention consequences of continued non-compliance

Return strictly valid JSON with keys: message_title, message_body
EOT;
        }

        return <<<EOT
Generate a WhatsApp reminder message for the following context:

Automation Type: {$library->name}
Frequency: {$library->frequency}
Language: {$language}
Tone: {$tone}
Description: {$library->description}

The message should remind a CA firm's client to submit their {$library->frequency} recurring compliance documents.

Return strictly valid JSON with keys: message_title, message_body
EOT;
    }

    private function getFallbackBody(CAAutomationLibrary $library, string $tone = 'professional', bool $isOverdue = false): string
    {
        if ($isOverdue) {
            return <<<EOT
Dear {{client_name}},

⚠️ *OVERDUE NOTICE — Immediate Action Required*

This is an urgent notice from {{firm_name}}. Your *{{document_name}}* submission is now *OVERDUE*.

📅 *Missed Deadline:* {{due_date}}
⏳ *Days Past Due:* {{days_remaining}} day(s)

The deadline for submitting your {$library->frequency} compliance documents has already passed. Continued delay may result in compliance penalties, processing failures, or regulatory consequences for your business.

Please submit your documents *immediately* by replying to this message or uploading directly through our portal.

For urgent assistance, contact our team right away.

Regards,
{{firm_name}}
EOT;
        }

        return <<<EOT
Dear {{client_name}},

This is a friendly reminder from {{firm_name}} regarding your {$library->frequency} compliance documents.

📋 *Documents Required:* {{document_name}}
📅 *Due Date:* {{due_date}}

Please ensure timely submission to avoid any compliance penalties.

For assistance, feel free to contact us.

Warm regards,
{{firm_name}}
EOT;
    }
}
