<?php

namespace Modules\CA\Livewire;

use Livewire\Component;
use Modules\CA\Models\CAClientComplianceDeadline;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ComplianceCalendar extends Component
{
    public function render()
    {
        $companyId = Auth::user()->company_id;
        
        $deadlinesQuery = CAClientComplianceDeadline::with(['clientCompliance.client.contact', 'clientCompliance.compliance'])
            ->whereHas('clientCompliance.client', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->whereNotNull('due_date');

        $deadlines = $deadlinesQuery->get();
            
        $stats = [
            'overdue' => 0,
            'pending_week' => 0,
            'completed_month' => 0,
        ];

        $now = Carbon::now();
        $endOfWeek = Carbon::now()->endOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Map deadlines to calendar events
        $events = $deadlines->map(function ($deadline) use (&$stats, $now, $endOfWeek, $startOfMonth) {
            $client = $deadline->clientCompliance->client;
            $compliance = $deadline->clientCompliance->compliance;

            // Determine precise status
            if ($deadline->completed_at) {
                $status = 'completed';
                if ($deadline->completed_at >= $startOfMonth) {
                    $stats['completed_month']++;
                }
            } else if ($deadline->due_date < $now->startOfDay()) {
                $status = 'overdue';
                $stats['overdue']++;
            } else {
                $status = 'pending';
                if ($deadline->due_date <= $endOfWeek) {
                    $stats['pending_week']++;
                }
            }

            return [
                'id' => $deadline->id,
                'title' => $client->client_name . ' - ' . $compliance->name,
                'start' => $deadline->due_date->format('Y-m-d'),
                'extendedProps' => [
                    'status' => $status,
                    'client_name' => $client->client_name,
                    'compliance_name' => $compliance->name,
                    'due_date_formatted' => $deadline->due_date->format('M d, Y'),
                    'deadline_name' => $deadline->deadline_name,
                    'contact_id' => $client->contact_id,
                    'workspace_url' => route('ca.clients.compliance.workspace', [
                        'clientId' => $client->id, 
                        'clientComplianceId' => $deadline->ca_client_compliance_id
                    ]),
                ]
            ];
        });

        return view('ca::livewire.compliance-calendar', [
            'events' => $events,
            'stats' => $stats
        ])->layout('layouts.panel');
    }
}
