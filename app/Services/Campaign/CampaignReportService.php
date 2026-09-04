<?php

namespace App\Services\Campaign;

use App\Models\Campaign\Campaign;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignReportService
{
    /**
     * Get summary metrics for a campaign.
     */
    public function getSummary(User $actor, Campaign $campaign): array
    {
        // Stats are already updated by recalculateStats
        return [
            'total' => $campaign->recipient_count,
            'eligible' => $campaign->eligible_recipient_count,
            'skipped' => $campaign->skipped_recipient_count,
            'pending' => $campaign->pending_count,
            'sent' => $campaign->sent_count,
            'delivered' => $campaign->delivered_count,
            'read' => $campaign->read_count,
            'failed' => $campaign->failed_count,
        ];
    }

    /**
     * List recipients for a campaign with filtering.
     */
    public function listRecipients(User $actor, Campaign $campaign, array $filters = []): LengthAwarePaginator
    {
        $query = $campaign->recipients()
            ->with(['contact', 'conversationMessage'])
            ->latest();

        if (!empty($filters['status'])) {
            $status = strtolower(trim($filters['status']));
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('phone', 'like', "%{$filters['search']}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 50);
    }

    /**
     * Export campaign recipients to CSV.
     */
    public function exportRecipientsCsv(User $actor, Campaign $campaign, array $filters = []): StreamedResponse
    {
        $fileName = "campaign_report_{$campaign->id}_" . now()->format('Ymd_His') . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($campaign, $filters) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Name', 'Phone', 'Status', 'Attempts', 
                'Sent At', 'Delivered At', 'Read At', 'Failed At', 
                'Skip Reason', 'Error Code', 'Error Message'
            ]);

            $query = $campaign->recipients()->orderBy('id');

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $query->chunk(1000, function($recipients) use ($file) {
                foreach ($recipients as $recipient) {
                    fputcsv($file, [
                        $recipient->name,
                        $recipient->phone,
                        $recipient->status,
                        $recipient->attempts,
                        $recipient->sent_at?->toDateTimeString(),
                        $recipient->delivered_at?->toDateTimeString(),
                        $recipient->read_at?->toDateTimeString(),
                        $recipient->failed_at?->toDateTimeString(),
                        $recipient->skip_reason,
                        $recipient->meta_error_code,
                        $recipient->meta_error_message,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get failure breakdown for a campaign.
     */
    public function getFailureBreakdown(User $actor, Campaign $campaign): array
    {
        $failures = $campaign->recipients()
            ->where('status', 'failed')
            ->selectRaw('meta_error_code, meta_error_message, count(*) as count')
            ->groupBy('meta_error_code', 'meta_error_message')
            ->orderByDesc('count')
            ->get();

        $skips = $campaign->recipients()
            ->where('status', 'skipped')
            ->selectRaw('skip_reason, count(*) as count')
            ->groupBy('skip_reason')
            ->orderByDesc('count')
            ->get();

        return [
            'failures' => $failures,
            'skips' => $skips,
        ];
    }
}
