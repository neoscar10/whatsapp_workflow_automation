<?php

namespace Modules\CA\Services;

use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Modules\CA\Models\CAClientAutomation;
use Illuminate\Support\Facades\Log;
use Exception;

class TemplateManagementService
{
    public function __construct(
        private WhatsAppTemplateService $whatsappTemplateService
    ) {}

    /**
     * Resolve, reuse, or submit a WhatsApp template for a client automation.
     */
    public function resolveTemplateForAutomation(CAClientAutomation $automation): ?WhatsAppTemplate
    {
        $library = $automation->automationLibrary;
        if (!$library) {
            return null;
        }

        // 1. Resolve connected WABA account for this company
        $account = WhatsAppAccount::where('company_id', $automation->company_id)
            ->where('connection_status', 'connected')
            ->first();

        if (!$account) {
            Log::warning("No active WhatsApp WABA account configured for company ID {$automation->company_id}. Cannot provision template.");
            return null;
        }

        // We use 'en_US' as default language code matching Meta requirements
        $languageCode = 'en_us';
        $tone = 'professional';

        // 2. Fetch the custom message or AI-generated baseline template text
        $customTitle = $automation->metadata_json['custom_message_title'] ?? null;
        $customBody = $automation->metadata_json['custom_message_body'] ?? null;

        if (empty($customTitle) || empty($customBody)) {
            // Fallback to cached AI template baseline if custom message isn't set yet
            $aiTemplateService = app(AutomationTemplateLibraryService::class);
            $baseline = $aiTemplateService->getOrGenerateTemplate($library, 'en', $tone);
            $customTitle = $baseline['message_title'];
            $customBody = $baseline['message_body'];
        }

        // Clean the template name string to meet Meta name requirements (lowercase, alphanumeric, underscores only)
        $expectedName = 'ca_' . strtolower(preg_replace('/[^a-z0-9_]/i', '_', $library->slug));

        $convertedBody = $this->convertPlaceholdersToMeta($customBody);

        // 3. Duplicate Prevention Strategy: Check if template with exact same body and header already exists
        $exactMatch = WhatsAppTemplate::where('whatsapp_account_id', $account->id)
            ->where('language_code', $languageCode)
            ->where('body_text', $convertedBody)
            ->where('header_text', $customTitle)
            ->first();

        if ($exactMatch && in_array(strtolower($exactMatch->status), ['approved', 'active'])) {
            $automation->update(['whatsapp_template_id' => $exactMatch->id]);
            return $exactMatch;
        }

        $existing = WhatsAppTemplate::where('whatsapp_account_id', $account->id)
            ->where('remote_template_name', $expectedName)
            ->where('language_code', $languageCode)
            ->first();

        if ($existing && in_array(strtolower($existing->status), ['approved', 'active'])) {
            // Link to existing and return
            $automation->update(['whatsapp_template_id' => $existing->id]);
            return $existing;
        }

        // If template exists but is rejected, allow editing/resubmitting
        if ($existing && strtolower($existing->status) === 'rejected') {
            try {
                $updated = $this->whatsappTemplateService->updateTemplateRecord(
                    $existing,
                    $account,
                    [
                        'category' => 'utility',
                        'language_code' => $languageCode,
                        'header_type' => 'text',
                        'header_text' => $customTitle,
                        'body_text' => $this->convertPlaceholdersToMeta($customBody),
                    ]
                );
                $automation->update(['whatsapp_template_id' => $updated->id]);
                return $updated;
            } catch (Exception $e) {
                Log::error("Failed to resubmit rejected template {$expectedName} for automation ID {$automation->id}: " . $e->getMessage());
            }
        }

        // 4. Create and Submit a new template if none exists
        if (!$existing) {
            try {
                $newTemplate = $this->whatsappTemplateService->createTemplate(
                    $account,
                    [
                        'remote_template_name' => $expectedName,
                        'category' => 'utility',
                        'language_code' => $languageCode,
                        'header_type' => 'text',
                        'header_text' => $customTitle,
                        'body_text' => $this->convertPlaceholdersToMeta($customBody),
                        'example_payload' => [
                            'header_text' => ['Client Name'],
                            'body_text' => ['Client Name', 'Firm Name', 'Document Name', 'Due Date', '3'],
                        ],
                    ]
                );

                $automation->update(['whatsapp_template_id' => $newTemplate->id]);
                return $newTemplate;
            } catch (Exception $e) {
                Log::error("Failed to submit new template {$expectedName} for WABA: " . $e->getMessage());
            }
        }

        return $existing;
    }

    /**
     * Resolve, reuse, or submit an overdue WhatsApp template for a client automation.
     */
    public function resolveOverdueTemplateForAutomation(CAClientAutomation $automation): ?WhatsAppTemplate
    {
        $library = $automation->automationLibrary;
        if (!$library) {
            return null;
        }

        // 1. Resolve connected WABA account for this company
        $account = WhatsAppAccount::where('company_id', $automation->company_id)
            ->where('connection_status', 'connected')
            ->first();

        if (!$account) {
            Log::warning("No active WhatsApp WABA account configured for company ID {$automation->company_id}. Cannot provision overdue template.");
            return null;
        }

        $languageCode = 'en_us';
        $tone = 'urgent'; // Default tone for overdue

        // 2. Fetch the custom overdue message template text
        $customTitle = $automation->metadata_json['custom_overdue_message_title'] ?? null;
        $customBody = $automation->metadata_json['custom_overdue_message_body'] ?? null;

        if (empty($customTitle) || empty($customBody)) {
            // Fallback baseline for overdue / urgent tone
            $aiTemplateService = app(AutomationTemplateLibraryService::class);
            $baseline = $aiTemplateService->getOrGenerateTemplate($library, 'en', $tone, false, true);
            $customTitle = $baseline['message_title'];
            $customBody = $baseline['message_body'];
        }

        // Clean the template name string and append _overdue
        $expectedName = 'ca_' . strtolower(preg_replace('/[^a-z0-9_]/i', '_', $library->slug)) . '_overdue';

        $convertedBody = $this->convertPlaceholdersToMeta($customBody);

        // 3. Duplicate Prevention Strategy
        $exactMatch = WhatsAppTemplate::where('whatsapp_account_id', $account->id)
            ->where('language_code', $languageCode)
            ->where('body_text', $convertedBody)
            ->where('header_text', $customTitle)
            ->first();

        if ($exactMatch && in_array(strtolower($exactMatch->status), ['approved', 'active'])) {
            $meta = $automation->metadata_json;
            $meta['whatsapp_overdue_template_id'] = $exactMatch->id;
            $automation->update(['metadata_json' => $meta]);
            return $exactMatch;
        }

        $existing = WhatsAppTemplate::where('whatsapp_account_id', $account->id)
            ->where('remote_template_name', $expectedName)
            ->where('language_code', $languageCode)
            ->first();

        if ($existing && in_array(strtolower($existing->status), ['approved', 'active'])) {
            $meta = $automation->metadata_json;
            $meta['whatsapp_overdue_template_id'] = $existing->id;
            $automation->update(['metadata_json' => $meta]);
            return $existing;
        }

        // If template exists but is rejected, resubmit
        if ($existing && strtolower($existing->status) === 'rejected') {
            try {
                $updated = $this->whatsappTemplateService->updateTemplateRecord(
                    $existing,
                    $account,
                    [
                        'category' => 'utility',
                        'language_code' => $languageCode,
                        'header_type' => 'text',
                        'header_text' => $customTitle,
                        'body_text' => $this->convertPlaceholdersToMeta($customBody),
                    ]
                );
                $meta = $automation->metadata_json;
                $meta['whatsapp_overdue_template_id'] = $updated->id;
                $automation->update(['metadata_json' => $meta]);
                return $updated;
            } catch (Exception $e) {
                Log::error("Failed to resubmit rejected overdue template {$expectedName} for automation ID {$automation->id}: " . $e->getMessage());
            }
        }

        // 4. Create and Submit a new template if none exists
        if (!$existing) {
            try {
                $newTemplate = $this->whatsappTemplateService->createTemplate(
                    $account,
                    [
                        'remote_template_name' => $expectedName,
                        'category' => 'utility',
                        'language_code' => $languageCode,
                        'header_type' => 'text',
                        'header_text' => $customTitle,
                        'body_text' => $this->convertPlaceholdersToMeta($customBody),
                        'example_payload' => [
                            'header_text' => ['Client Name'],
                            'body_text' => ['Client Name', 'Firm Name', 'Document Name', 'Due Date', '3'],
                        ],
                    ]
                );

                $meta = $automation->metadata_json;
                $meta['whatsapp_overdue_template_id'] = $newTemplate->id;
                $automation->update(['metadata_json' => $meta]);
                return $newTemplate;
            } catch (Exception $e) {
                Log::error("Failed to submit new overdue template {$expectedName} for WABA: " . $e->getMessage());
            }
        }

        return $existing;
    }

    /**
     * Helper to map double curly braces (e.g. {{client_name}}) to Meta numbered variables (e.g. {{1}}, {{2}}).
     */
    public function convertPlaceholdersToMeta(string $body): string
    {
        $patterns = [
            '/\{\{client_name\}\}/i'      => '{{1}}',
            '/\{\{firm_name\}\}/i'        => '{{2}}',
            '/\{\{document_names?\}\}/i'  => '{{3}}',
            '/\{\{due_date\}\}/i'         => '{{4}}',
            '/\{\{days_remained\}\}/i'    => '{{5}}',
            '/\{\{days_remaining\}\}/i'   => '{{5}}',
            '/\{\{upload_link\}\}/i'      => '{{6}}',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $body);
    }
}
