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
                'audience_type' => $selection['type'] ?? 'mixed',
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

        $type = $selection['type'] ?? 'selected_contacts';

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
}
