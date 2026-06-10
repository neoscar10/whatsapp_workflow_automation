<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CADocument;
use Modules\CA\Models\CAClientComplianceDeadline;
use Illuminate\Support\Facades\DB;

class ComplianceDashboardService
{
    /**
     * Get aggregated metrics for the CA operations dashboard.
     */
    public function getDashboardMetrics(int $companyId): array
    {
        $clientsQuery = CAClient::where('company_id', $companyId);
        
        $compliancesQuery = CAClientCompliance::whereHas('client', function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });

        // Fast aggregations
        $totalClients = $clientsQuery->count();
        
        $complianceStats = $compliancesQuery->select('health_status', DB::raw('count(*) as count'))
            ->groupBy('health_status')
            ->pluck('count', 'health_status')
            ->toArray();

        $activeCompliances = array_sum($complianceStats);
        
        $overdueCompliances = $complianceStats['overdue'] ?? 0;
        $atRiskCompliances = $complianceStats['at_risk'] ?? 0;
        $completedCompliances = $complianceStats['completed'] ?? 0;

        $documentsPendingReview = CADocument::whereHas('requirement.clientCompliance.client', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('status', 'submitted')
            ->count();

        $upcomingDeadlines = CAClientComplianceDeadline::whereHas('clientCompliance.client', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->count();

        return [
            'total_clients' => $totalClients,
            'active_compliances' => $activeCompliances,
            'completed_compliances' => $completedCompliances,
            'overdue_compliances' => $overdueCompliances,
            'at_risk_compliances' => $atRiskCompliances,
            'documents_pending_review' => $documentsPendingReview,
            'upcoming_deadlines' => $upcomingDeadlines,
        ];
    }
}
