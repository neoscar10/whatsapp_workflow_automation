<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CAClientComplianceDeadline;
use Carbon\Carbon;

class DeadlineService
{
    /**
     * Calculate and snapshot deadlines for a client compliance
     */
    public function generateDeadlines(CAClientCompliance $clientCompliance): void
    {
        $compliance = $clientCompliance->compliance;
        $deadlines = $compliance->complianceDeadlines; // the master deadlines

        foreach ($deadlines as $masterDeadline) {
            
            $dueDate = Carbon::now()->addDays(30)->toDateString();
            $deadlineName = $masterDeadline->description ?? ($compliance->name . ' Due');

            CAClientComplianceDeadline::firstOrCreate(
                [
                    'ca_client_compliance_id' => $clientCompliance->id,
                    'deadline_name' => $deadlineName,
                ],
                [
                    'deadline_type' => 'Standard',
                    'due_date' => $dueDate,
                    'status' => 'pending',
                ]
            );
        }
    }

    public function generateRecurringDeadlines(\Modules\CA\Models\CAClientComplianceRequirement $requirement): void
    {
        if (!$requirement->is_recurring || !$requirement->recurrence_frequency) {
            return;
        }

        $dueDateStr = $requirement->next_due_date;
        
        // If config exists, try calculating it live (in case it wasn't calculated yet or needs refresh)
        $config = $requirement->recurrence_config;
        if (is_string($config)) {
            $config = json_decode($config, true);
        }
        
        if (is_array($config) && !empty($config)) {
            $calculated = $this->calculateNextDueDate($requirement->recurrence_frequency, $config);
            if ($calculated) {
                $dueDateStr = $calculated->toDateString();
                if ($requirement->next_due_date !== $dueDateStr) {
                    $requirement->updateQuietly(['next_due_date' => $dueDateStr]);
                }
            }
        }

        if (!$dueDateStr) {
            return;
        }

        $dueDate = Carbon::parse($dueDateStr)->toDateString();
        $deadlineName = $requirement->name . ' - ' . ucfirst($requirement->recurrence_frequency) . ' Due';

        CAClientComplianceDeadline::updateOrCreate(
            [
                'ca_client_compliance_id' => $requirement->ca_client_compliance_id,
                'ca_client_compliance_requirement_id' => $requirement->id,
                'status' => 'pending',
            ],
            [
                'deadline_name' => $deadlineName,
                'deadline_type' => 'Recurring',
                'due_date' => $dueDate,
            ]
        );
    }

    public function calculateNextDueDate(string $frequency, array $config, ?Carbon $from = null): ?Carbon
    {
        $now = $from ?? Carbon::today();
        
        try {
            switch (strtolower($frequency)) {
                case 'weekly':
                    if (empty($config['days']) || !is_array($config['days'])) return null;
                    $nextDates = [];
                    foreach ($config['days'] as $dayName) {
                        $date = clone $now;
                        if (strtolower($date->englishDayOfWeek) !== strtolower($dayName)) {
                            $date->next($dayName);
                        }
                        $nextDates[] = $date;
                    }
                    usort($nextDates, fn($a, $b) => $a->timestamp <=> $b->timestamp);
                    return current($nextDates);

                case 'monthly':
                    $day = (int)($config['day_of_month'] ?? 0);
                    if (!$day) return null;
                    
                    $candidate = clone $now;
                    $daysInMonth = $candidate->daysInMonth;
                    $targetDay = min($day, $daysInMonth);
                    $candidate->day($targetDay);
                    
                    if ($candidate->isBefore($now)) {
                        $candidate->addMonthNoOverflow();
                        $targetDay = min($day, $candidate->daysInMonth);
                        $candidate->day($targetDay);
                    }
                    return $candidate;

                case 'quarterly':
                    $qType = $config['quarter_type'] ?? 'calendar';
                    $dueDays = (int)($config['due_days_after_quarter_end'] ?? 0);
                    
                    $candidate = clone $now;
                    
                    if ($qType === 'financial') {
                        $month = $candidate->month;
                        if ($month <= 3) {
                            $candidate->month(3)->endOfMonth();
                        } elseif ($month <= 6) {
                            $candidate->month(6)->endOfMonth();
                        } elseif ($month <= 9) {
                            $candidate->month(9)->endOfMonth();
                        } else {
                            $candidate->month(12)->endOfMonth();
                        }
                    } else {
                        $candidate->lastOfQuarter();
                    }
                    
                    $candidate->startOfDay()->addDays($dueDays);
                    
                    if ($candidate->isBefore($now)) {
                        $candidate->subDays($dueDays)->addMonthsNoOverflow(3);
                        if ($qType === 'financial') {
                            $candidate->endOfMonth();
                        } else {
                            $candidate->lastOfQuarter();
                        }
                        $candidate->startOfDay()->addDays($dueDays);
                    }
                    
                    return $candidate;

                case 'yearly':
                    $month = (int)($config['month'] ?? 0);
                    $day = (int)($config['day'] ?? 0);
                    if (!$month || !$day) return null;
                    
                    $candidate = clone $now;
                    $daysInMonth = Carbon::create($candidate->year, $month, 1)->daysInMonth;
                    $targetDay = min($day, $daysInMonth);
                    
                    $candidate->setDate($candidate->year, $month, $targetDay)->startOfDay();
                    
                    if ($candidate->isBefore($now)) {
                        $candidate->addYear();
                        $daysInMonth = Carbon::create($candidate->year, $month, 1)->daysInMonth;
                        $targetDay = min($day, $daysInMonth);
                        $candidate->setDate($candidate->year, $month, $targetDay)->startOfDay();
                    }
                    
                    return $candidate;
                    
                case 'custom':
                    $interval = (int)($config['interval'] ?? 0);
                    $unit = $config['unit'] ?? 'days';
                    $startDate = isset($config['start_date']) ? Carbon::parse($config['start_date']) : clone $now;
                    
                    if (!$interval) return null;
                    
                    $candidate = clone $startDate;
                    while ($candidate->isBefore($now)) {
                        if ($unit === 'weeks') {
                            $candidate->addWeeks($interval);
                        } elseif ($unit === 'months') {
                            $candidate->addMonthsNoOverflow($interval);
                        } else {
                            $candidate->addDays($interval);
                        }
                    }
                    return $candidate;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}
