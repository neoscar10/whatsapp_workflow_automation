<?php

namespace App\Services\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\Campaign\CampaignRecipient;
use App\Models\Contact\Contact;
use App\Models\User;
use App\Support\PhoneNumberNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class CampaignAudienceService
{
    /**
     * Preview audience for a campaign selection.
     */
    public function previewAudience(User $actor, array $selection): array
    {
        $contacts = $this->resolveContacts($actor->company_id, $selection);
        
        $totalMatched = $contacts->count();
        $eligible = 0;
        $skipped = 0;
        $skipReasons = [];

        foreach ($contacts as $contact) {
            if ($contact->isMessageable()) {
                $eligible++;
            } else {
                $skipped++;
                $reason = $this->explainSkipReason($contact);
                $skipReasons[$reason] = ($skipReasons[$reason] ?? 0) + 1;
            }
        }

        return [
            'total_matched' => $totalMatched,
            'eligible' => $eligible,
            'skipped' => $skipped,
            'skip_reasons' => $skipReasons,
            'sample' => $contacts->take(5)->map(fn($c) => [
                'name' => $c->name,
                'phone' => $c->phone,
                'status' => $c->status,
                'is_messageable' => $c->isMessageable()
            ]),
        ];
    }

    /**
     * Sync audience for a campaign.
     */
    public function syncAudience(User $actor, Campaign $campaign, array $selection): void
    {
        if (!$campaign->isDraft()) {
            throw new Exception("Audience can only be modified for draft campaigns.");
        }

        DB::transaction(function () use ($actor, $campaign, $selection) {
            // Clear existing recipients
            $campaign->recipients()->delete();

            $contacts = $this->resolveContacts($actor->company_id, $selection);
            
            $recipients = [];
            $phonesAdded = [];

            foreach ($contacts as $contact) {
                $normalized = PhoneNumberNormalizer::normalize($contact->phone);
                
                // Prevent duplicates within the campaign
                if (in_array($normalized, $phonesAdded)) {
                    continue;
                }
                $phonesAdded[] = $normalized;

                $isMessageable = $contact->isMessageable();
                
                $recipients[] = [
                    'campaign_id' => $campaign->id,
                    'company_id' => $actor->company_id,
                    'contact_id' => $contact->id,
                    'phone' => $contact->phone,
                    'normalized_phone' => $normalized,
                    'name' => $contact->name,
                    'status' => $isMessageable ? 'pending' : 'skipped',
                    'skip_reason' => $isMessageable ? null : $this->explainSkipReason($contact),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Batch insert if needed
                if (count($recipients) >= 500) {
                    CampaignRecipient::insert($recipients);
                    $recipients = [];
                }
            }

            if (!empty($recipients)) {
                CampaignRecipient::insert($recipients);
            }

            // Update campaign counters
            $campaign->update([
                'audience_type' => $selection['audience_type'] ?? $selection['type'] ?? 'mixed',
                'audience_filters' => $selection['filters'] ?? [],
            ]);

            app(CampaignService::class)->recalculateStats($campaign);
        });
    }

    /**
     * Resolve contacts based on selection criteria.
     */
    public function resolveContacts(int $companyId, array $selection): Collection
    {
        $query = Contact::forCompany($companyId);

        $type = $selection['audience_type'] ?? $selection['type'] ?? 'selected_contacts';

        switch ($type) {
            case 'selected_contacts':
                $query->whereIn('id', $selection['contact_ids'] ?? []);
                break;
            case 'groups':
                $groupIds = $selection['group_ids'] ?? [];
                $groups = \App\Models\Contact\ContactGroup::whereIn('id', $groupIds)->get();
                
                $query->where(function ($q) use ($groups, $companyId) {
                    foreach ($groups as $group) {
                        if ($group->type === 'dynamic') {
                            $q->orWhere(function ($sq) use ($group, $companyId) {
                                app(\App\Services\Contact\ContactSegmentRuleService::class)->applyRulesToQuery($sq, $companyId, $group->rules ?? []);
                            });
                        } else {
                            $q->orWhereHas('groups', fn($gq) => $gq->where('contact_groups.id', $group->id));
                        }
                    }
                });
                break;
            case 'filters':
                $this->applyFilters($query, $selection['filters'] ?? []);
                break;
            default:
                // Mixed or custom handling if needed
                break;
        }

        return $query->get();
    }

    /**
     * Apply filters to the contact query.
     */
    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['has_opted_in'])) {
            $query->where('has_opted_in', (bool)$filters['has_opted_in']);
        }
        if (isset($filters['do_not_message'])) {
            $query->where('do_not_message', (bool)$filters['do_not_message']);
        }
        if (!empty($filters['group_ids'])) {
            $query->whereHas('groups', fn($q) => $q->whereIn('contact_groups.id', $filters['group_ids']));
        }
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
    }

    /**
     * Explain why a contact is skipped.
     */
    public function explainSkipReason(Contact $contact): string
    {
        if ($contact->status === 'blocked') {
            return 'Contact is blocked';
        }
        if ($contact->do_not_message) {
            return 'Do not message enabled';
        }
        if ($contact->opted_out_at) {
            return 'Contact opted out';
        }
        if (!PhoneNumberNormalizer::isValid($contact->phone)) {
            return 'Invalid phone number';
        }
        return 'Unknown reason';
    }

    /**
     * Add manual recipients to a campaign.
     */
    public function addManualRecipients(User $actor, Campaign $campaign, array $rows): array
    {
        if (!$campaign->isDraft()) {
            throw new Exception("Audience can only be modified for draft campaigns.");
        }

        $addedCount = 0;
        $recipients = [];
        $phonesAdded = $campaign->recipients()->pluck('normalized_phone')->toArray();

        foreach ($rows as $row) {
            $rawPhone = trim($row['phone'] ?? '');
            if (empty($rawPhone)) {
                continue;
            }

            $normalized = PhoneNumberNormalizer::normalize($rawPhone);
            if (in_array($normalized, $phonesAdded)) {
                continue;
            }
            $phonesAdded[] = $normalized;

            $contact = Contact::where('company_id', $actor->company_id)
                ->where(function ($q) use ($rawPhone, $normalized) {
                    $q->where('phone', $rawPhone)->orWhere('phone', $normalized);
                })->first();

            $isValid = PhoneNumberNormalizer::isValid($rawPhone);
            $skipReason = null;
            if (!$isValid) {
                $skipReason = 'Invalid phone number format';
            } elseif ($contact && !$contact->isMessageable()) {
                $skipReason = $this->explainSkipReason($contact);
            }

            $recipients[] = [
                'campaign_id' => $campaign->id,
                'company_id' => $actor->company_id,
                'contact_id' => $contact?->id,
                'phone' => $rawPhone,
                'normalized_phone' => $normalized,
                'name' => $row['name'] ?? $contact?->name,
                'personalization_data' => isset($row['variables']) ? json_encode($row['variables']) : null,
                'status' => $skipReason ? 'skipped' : 'pending',
                'skip_reason' => $skipReason,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $addedCount++;
        }

        if (!empty($recipients)) {
            CampaignRecipient::insert($recipients);
            $campaign->update(['audience_type' => 'manual']);
            app(CampaignService::class)->recalculateStats($campaign);
        }

        return [
            'added_count' => $addedCount,
            'total_recipients' => $campaign->recipients()->count(),
        ];
    }

    /**
     * Detailed validation & preview of campaign recipients (pass/fail per row).
     */
    public function validateAndPreviewRecipients(User $actor, Campaign $campaign): array
    {
        $recipients = $campaign->recipients()->get();
        $total = $recipients->count();
        $passed = 0;
        $failed = 0;

        $detailedRows = $recipients->map(function ($r) use (&$passed, &$failed) {
            $isValidPhone = PhoneNumberNormalizer::isValid($r->phone);
            $isSkipped = $r->status === 'skipped';
            $isPassed = $isValidPhone && !$isSkipped;

            if ($isPassed) {
                $passed++;
            } else {
                $failed++;
            }

            return [
                'id' => $r->id,
                'phone' => $r->phone,
                'normalized_phone' => $r->normalized_phone,
                'name' => $r->name,
                'status' => $r->status,
                'is_valid' => $isPassed,
                'validation_status' => $isPassed ? 'passed' : 'failed',
                'error_reason' => $r->skip_reason ?: (!$isValidPhone ? 'Invalid phone number format' : 'Validation issue'),
                'personalization_data' => $r->personalization_data,
            ];
        });

        return [
            'total' => $total,
            'passed_count' => $passed,
            'failed_count' => $failed,
            'rows' => $detailedRows,
        ];
    }

    /**
     * Correct an individual recipient row.
     */
    public function correctRecipientRow(User $actor, Campaign $campaign, int $recipientId, array $data): array
    {
        $recipient = $campaign->recipients()->where('id', $recipientId)->firstOrFail();

        $rawPhone = trim($data['phone'] ?? $recipient->phone);
        $normalized = PhoneNumberNormalizer::normalize($rawPhone);
        $name = $data['name'] ?? $recipient->name;
        $variables = $data['variables'] ?? null;

        $isValid = PhoneNumberNormalizer::isValid($rawPhone);
        
        $contact = Contact::where('company_id', $actor->company_id)
            ->where(function ($q) use ($rawPhone, $normalized) {
                $q->where('phone', $rawPhone)->orWhere('phone', $normalized);
            })->first();

        $skipReason = null;
        if (!$isValid) {
            $skipReason = 'Invalid phone number format';
        } elseif ($contact && !$contact->isMessageable()) {
            $skipReason = $this->explainSkipReason($contact);
        }

        $updateData = [
            'phone' => $rawPhone,
            'normalized_phone' => $normalized,
            'name' => $name,
            'status' => $skipReason ? 'skipped' : 'pending',
            'skip_reason' => $skipReason,
        ];

        if ($variables !== null) {
            $updateData['personalization_data'] = is_array($variables) ? json_encode($variables) : $variables;
        }

        $recipient->update($updateData);
        app(CampaignService::class)->recalculateStats($campaign);

        return [
            'id' => $recipient->id,
            'phone' => $recipient->phone,
            'name' => $recipient->name,
            'is_valid' => empty($skipReason),
            'validation_status' => empty($skipReason) ? 'passed' : 'failed',
            'error_reason' => $skipReason,
        ];
    }
}
