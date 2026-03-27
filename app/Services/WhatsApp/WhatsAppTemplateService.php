<?php

namespace App\Services\WhatsApp;

use App\Models\Company;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppTemplateService
{
    protected MetaTemplateApiService $apiService;
    protected TemplatePayloadBuilder $payloadBuilder;

    public function __construct(MetaTemplateApiService $apiService, TemplatePayloadBuilder $payloadBuilder)
    {
        $this->apiService = $apiService;
        $this->payloadBuilder = $payloadBuilder;
    }

    /**
     * Lists templates for a company (local database query).
     */
    public function listTemplatesForCompany(Company $company, array $filters = [])
    {
        $query = WhatsAppTemplate::where('company_id', $company->id)
            ->with(['account', 'buttons'])
            ->orderByDesc('created_at');

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('remote_template_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('display_title', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['language'])) {
            $query->where('language_code', $filters['language']);
        }

        return $query->paginate(15);
    }

    /**
     * Finds a specific template by ID, scoped to the company.
     */
    public function findTemplateForCompany(Company $company, int $templateId): ?WhatsAppTemplate
    {
        return WhatsAppTemplate::where('company_id', $company->id)
            ->with(['account', 'buttons'])
            ->findOrFail($templateId);
    }

    /**
     * Synchronizes all templates from Meta for a specific account.
     */
    public function syncTemplatesFromMeta(WhatsAppAccount $account): array
    {
        try {
            $remoteTemplates = $this->apiService->fetchAllTemplates($account);
            $syncedCount = 0;

            DB::beginTransaction();

            foreach ($remoteTemplates as $remoteData) {
                // Parse components first to get required fields like body_text
                $parsed = $this->parseMetaComponents($remoteData['components'] ?? []);

                $template = WhatsAppTemplate::updateOrCreate(
                    [
                        'company_id' => $account->company_id,
                        'whatsapp_account_id' => $account->id,
                        'remote_template_name' => $remoteData['name'],
                        'language_code' => $remoteData['language'],
                    ],
                    [
                        'remote_template_id' => $remoteData['id'],
                        'display_title' => $this->generateDisplayTitle($remoteData['name']),
                        'category' => strtolower($remoteData['category']),
                        'status' => strtolower($remoteData['status']),
                        'quality_rating' => $remoteData['quality_score']['score'] ?? null,
                        'rejection_reason' => $remoteData['reason'] ?? null,
                        'header_type' => $parsed['header_type'],
                        'header_text' => $parsed['header_text'],
                        'body_text' => $parsed['body_text'],
                        'footer_text' => $parsed['footer_text'],
                        'button_count' => $parsed['button_count'],
                        'meta_payload' => $remoteData,
                        'last_synced_at' => now(),
                    ]
                );

                // Sync buttons separately
                $this->syncTemplateButtons($template, $parsed['buttons']);
                
                $syncedCount++;
            }

            DB::commit();
            return ['status' => "Successfully synced {$syncedCount} templates."];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Template Sync Failed', ['error' => $e->getMessage(), 'account_id' => $account->id]);
            throw $e;
        }
    }

    /**
     * Creates a new template locally and attempts to push to Meta.
     */
    public function createTemplate(WhatsAppAccount $account, array $data, array $buttons = [], int $userId = null, $headerSampleFile = null): WhatsAppTemplate
    {
        // 1. Validate name format (lowercase alphanumeric and underscores only)
        $data['remote_template_name'] = strtolower(preg_replace('/[^a-z0-9_]/i', '_', $data['remote_template_name']));

        DB::beginTransaction();
        try {
            // Handle Media Header Upload to Meta if sample provided
            if ($headerSampleFile && in_array($data['header_type'] ?? '', ['image', 'video', 'document'])) {
                $mediaService = app(MetaMediaUploadService::class);
                $appId = config('services.whatsapp.app_id');
                
                if (!$appId) {
                    throw new \Exception("WhatsApp App ID is not configured in services.php. Required for media handles.");
                }

                $handle = $mediaService->uploadTemplateSample($account->access_token, $appId, $headerSampleFile);
                $data['example_payload']['header_handle'] = [$handle];
            }

            // 2. Create local draft
            $template = WhatsAppTemplate::create([
                'company_id' => $account->company_id,
                'whatsapp_account_id' => $account->id,
                'remote_template_name' => $data['remote_template_name'],
                'display_title' => $data['display_title'] ?? $this->generateDisplayTitle($data['remote_template_name']),
                'category' => strtolower($data['category']),
                'language_code' => $data['language_code'],
                'status' => 'draft',
                'header_type' => $data['header_type'] ?? 'none',
                'header_text' => $data['header_text'] ?? null,
                'body_text' => $data['body_text'],
                'footer_text' => $data['footer_text'] ?? null,
                'button_count' => count($buttons),
                'created_by_user_id' => $userId,
            ]);

            // Save buttons
            foreach ($buttons as $index => $button) {
                $template->buttons()->create(array_merge($button, ['sort_order' => $index]));
            }

            // 3. Build Payload
            $payload = $this->payloadBuilder->build($template->toArray() + ['example_payload' => $data['example_payload'] ?? []], $buttons);

            // 4. Push to Meta
            $metaResponse = $this->apiService->createTemplate($account, $payload);

            // 5. Update local record with Meta ID and new status
            $template->update([
                'remote_template_id' => $metaResponse['id'],
                'status' => strtolower($metaResponse['status'] ?? 'pending'), // usually pending after creation
                'submitted_at' => now(),
                'last_synced_at' => now(),
            ]);

            DB::commit();
            return $template->fresh(['buttons']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Template Creation Failed', [
                'error' => $e->getMessage(), 
                'data' => $data, 
                'account_id' => $account->id
            ]);
            throw $e;
        }
    }

    public function updateTemplateRecord(WhatsAppTemplate $template, WhatsAppAccount $account, array $data, array $buttons = [], int $userId = null, $headerSampleFile = null): WhatsAppTemplate
    {
        DB::beginTransaction();
        try {
            // Handle Media Header Upload if updated
            if ($headerSampleFile && in_array($data['header_type'] ?? '', ['image', 'video', 'document'])) {
                $mediaService = app(MetaMediaUploadService::class);
                $appId = config('services.whatsapp.app_id');
                $handle = $mediaService->uploadTemplateSample($account->access_token, $appId, $headerSampleFile);
                $data['example_payload']['header_handle'] = [$handle];
            }

            // Meta API Update Flow requires identical payload builder logic
            $payloadData = array_merge($template->toArray(), $data); // Merge existing with new for builder safety
            $payload = $this->payloadBuilder->build($payloadData, $buttons);

            // Push to Meta (uses POST to message_templates/{id})
            if ($template->remote_template_id) {
                $metaResponse = $this->apiService->updateTemplate($account, $template->remote_template_id, $payload);
            } else {
                // Failsafe: if drafting, we might be creating it on Meta for the first time
                $metaResponse = $this->apiService->createTemplate($account, $payload);
            }
            
            // Update local DB
            $template->update([
                 'category' => strtolower($data['category']),
                 'status' => 'pending', // Reverts to pending when edited
                 'header_type' => $data['header_type'] ?? 'none',
                 'header_text' => $data['header_text'] ?? null,
                 'body_text' => $data['body_text'],
                 'footer_text' => $data['footer_text'] ?? null,
                 'button_count' => count($buttons),
                 'updated_by_user_id' => $userId,
                 'remote_template_id' => $metaResponse['id'] ?? $template->remote_template_id,
            ]);

            // Replace buttons
            $template->buttons()->delete();
            foreach ($buttons as $index => $button) {
                $template->buttons()->create(array_merge($button, ['sort_order' => $index]));
            }

            DB::commit();
            return $template->fresh(['buttons']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Template Update Failed', [
                'error' => $e->getMessage(), 
                'template_id' => $template->id
            ]);
            throw $e;
        }
    }

    /**
     * Soft deletes a template from Meta and locally.
     */
    public function deleteTemplate(WhatsAppTemplate $template): void
    {
        $account = $template->account;

        if ($template->remote_template_name) {
            try {
                $this->apiService->deleteTemplate($account, $template->remote_template_name);
            } catch (\Exception $e) {
                // If it fails on Meta (e.g., doesn't exist), we log it but still delete locally 
                // so the user isn't stuck with a zombie record.
                Log::warning('Failed to delete template from Meta during local deletion', [
                    'template' => $template->remote_template_name,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $template->delete();
    }

    /**
     * Helper to generate a human-readable title from a technical template name.
     */
    protected function generateDisplayTitle(string $name): string
    {
        return ucwords(str_replace('_', ' ', $name));
    }

    /**
     * Parses Meta component structure into a flat array.
     */
    protected function parseMetaComponents(array $components): array
    {
        $data = [
            'header_type' => 'none',
            'header_text' => null,
            'body_text' => '',
            'footer_text' => null,
            'button_count' => 0,
            'buttons' => [],
        ];

        foreach ($components as $component) {
            switch ($component['type']) {
                case 'HEADER':
                    $data['header_type'] = strtolower($component['format']);
                    if ($data['header_type'] === 'text') {
                        $data['header_text'] = $component['text'] ?? null;
                    }
                    break;
                case 'BODY':
                    $data['body_text'] = $component['text'] ?? '';
                    break;
                case 'FOOTER':
                    $data['footer_text'] = $component['text'] ?? null;
                    break;
                case 'BUTTONS':
                    if (isset($component['buttons'])) {
                        foreach ($component['buttons'] as $btn) {
                            $data['buttons'][] = [
                                'type' => strtolower($btn['type']),
                                'text' => $btn['text'] ?? '',
                                'url' => $btn['url'] ?? null,
                                'phone_number' => $btn['phone_number'] ?? null,
                            ];
                        }
                    }
                    $data['button_count'] = count($data['buttons']);
                    break;
            }
        }

        return $data;
    }

    /**
     * Syncs buttons for a template.
     */
    protected function syncTemplateButtons(WhatsAppTemplate $template, array $buttons): void
    {
        $template->buttons()->delete();
        foreach ($buttons as $index => $btn) {
            $template->buttons()->create(array_merge($btn, ['sort_order' => $index]));
        }
    }

    /**
     * Legacy method - kept for backward compatibility if needed, but uses the new logic.
     */
    protected function parseAndSaveComponentsFromMeta(WhatsAppTemplate $template, array $components): void
    {
        $parsed = $this->parseMetaComponents($components);
        
        $template->update([
            'header_type' => $parsed['header_type'],
            'header_text' => $parsed['header_text'],
            'body_text' => $parsed['body_text'],
            'footer_text' => $parsed['footer_text'],
            'button_count' => $parsed['button_count'],
        ]);

        $this->syncTemplateButtons($template, $parsed['buttons']);
    }
}
