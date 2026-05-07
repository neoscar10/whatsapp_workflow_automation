<?php

namespace App\Services\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppTemplate;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Exception;

class CampaignService
{
    /**
     * List campaigns for a company.
     */
    public function listForCompany(User $actor, array $filters = []): LengthAwarePaginator
    {
        $query = Campaign::forCompany($actor->company_id)
            ->with(['creator', 'whatsappTemplate'])
            ->latest();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Find a campaign for a company.
     */
    public function findForCompany(User $actor, int $campaignId): Campaign
    {
        return Campaign::forCompany($actor->company_id)
            ->findOrFail($campaignId);
    }

    /**
     * Create a draft campaign.
     */
    public function createDraft(User $actor, array $data): Campaign
    {
        $data['company_id'] = $actor->company_id;
        $data['created_by'] = $actor->id;
        $data['status'] = 'draft';
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);

        return Campaign::create($data);
    }

    /**
     * Update a campaign (only if it can be edited).
     */
    public function update(User $actor, Campaign $campaign, array $data): Campaign
    {
        if (!$campaign->canEdit()) {
            throw new Exception("This campaign cannot be edited in its current status: {$campaign->status}");
        }

        $data['updated_by'] = $actor->id;
        $campaign->update($data);

        return $campaign;
    }

    /**
     * Update campaign content (template or text).
     */
    public function updateContent(User $actor, Campaign $campaign, array $data): Campaign
    {
        if ($data['type'] === 'template') {
            $template = WhatsAppTemplate::where('company_id', $actor->company_id)
                ->findOrFail($data['whatsapp_template_id']);

            $campaign->update([
                'type' => 'template',
                'whatsapp_template_id' => $template->id,
                'template_name' => $template->remote_template_name,
                'template_language' => $template->language_code,
                'template_components' => $template->components ?? [], // Assuming components exist on template
                'template_variable_mapping' => $data['template_variable_mapping'] ?? [],
                'default_variable_values' => $data['default_variable_values'] ?? [],
            ]);
        } else {
            $campaign->update([
                'type' => 'text',
                'message_body' => $data['message_body'],
                'whatsapp_template_id' => null,
            ]);
        }

        return $campaign;
    }

    /**
     * Schedule a campaign.
     */
    public function schedule(User $actor, Campaign $campaign, $scheduledAt): Campaign
    {
        if (!$campaign->isDraft()) {
            throw new Exception("Only draft campaigns can be scheduled.");
        }

        if ($campaign->recipient_count === 0) {
            throw new Exception("Campaign must have an audience before scheduling.");
        }

        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'updated_by' => $actor->id,
        ]);

        return $campaign;
    }

    /**
     * Pause a sending campaign.
     */
    public function pause(User $actor, Campaign $campaign): Campaign
    {
        if (!$campaign->canPause()) {
            throw new Exception("This campaign cannot be paused.");
        }

        $campaign->update([
            'status' => 'paused',
            'paused_at' => now(),
            'updated_by' => $actor->id,
        ]);

        return $campaign;
    }

    /**
     * Resume a paused campaign.
     */
    public function resume(User $actor, Campaign $campaign): Campaign
    {
        if (!$campaign->isPaused()) {
            throw new Exception("Only paused campaigns can be resumed.");
        }

        $campaign->update([
            'status' => 'queued', // Move to queued so dispatcher picks it up
            'updated_by' => $actor->id,
        ]);

        return $campaign;
    }

    /**
     * Cancel a campaign.
     */
    public function cancel(User $actor, Campaign $campaign): Campaign
    {
        if (!$campaign->canCancel()) {
            throw new Exception("This campaign cannot be cancelled.");
        }

        $campaign->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'updated_by' => $actor->id,
        ]);

        // Mark all pending recipients as cancelled
        $campaign->recipients()->where('status', 'pending')->update(['status' => 'cancelled']);

        return $campaign;
    }

    /**
     * Duplicate a campaign.
     */
    public function duplicate(User $actor, Campaign $campaign): Campaign
    {
        $newCampaign = $campaign->replicate([
            'status', 'recipient_count', 'eligible_recipient_count', 'skipped_recipient_count',
            'sent_count', 'delivered_count', 'read_count', 'failed_count', 'pending_count',
            'started_at', 'completed_at', 'paused_at', 'cancelled_at', 'last_dispatched_at',
            'failure_reason', 'created_at', 'updated_at'
        ]);

        $newCampaign->name = "Copy of " . $campaign->name;
        $newCampaign->slug = Str::slug($newCampaign->name) . '-' . Str::random(5);
        $newCampaign->status = 'draft';
        $newCampaign->created_by = $actor->id;
        $newCampaign->company_id = $actor->company_id;
        $newCampaign->save();

        return $newCampaign;
    }

    /**
     * Delete a campaign.
     */
    public function deleteForCompany(User $actor, Campaign $campaign): bool
    {
        if ($campaign->isSending() || $campaign->status === 'queued') {
            throw new Exception("Cancel this campaign before deleting it.");
        }

        return $campaign->delete();
    }

    /**
     * Recalculate campaign stats.
     */
    public function recalculateStats(Campaign $campaign): void
    {
        $stats = $campaign->recipients()
            ->selectRaw('count(*) as total')
            ->selectRaw('count(case when status = "pending" then 1 end) as pending')
            ->selectRaw('count(case when status = "sent" then 1 end) as sent')
            ->selectRaw('count(case when status = "delivered" then 1 end) as delivered')
            ->selectRaw('count(case when status = "read" then 1 end) as read_count')
            ->selectRaw('count(case when status = "failed" then 1 end) as failed')
            ->selectRaw('count(case when status = "skipped" then 1 end) as skipped')
            ->first();

        $campaign->update([
            'recipient_count' => $stats->total,
            'pending_count' => $stats->pending,
            'sent_count' => $stats->sent,
            'delivered_count' => $stats->delivered,
            'read_count' => $stats->read_count,
            'failed_count' => $stats->failed,
            'skipped_recipient_count' => $stats->skipped,
            'eligible_recipient_count' => $stats->total - $stats->skipped,
        ]);

        // Auto-complete if everything is terminal
        if ($campaign->status === 'sending' && $stats->pending === 0) {
            $campaign->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
        }
    }
}
