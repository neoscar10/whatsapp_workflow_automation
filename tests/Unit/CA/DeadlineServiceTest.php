<?php

namespace Tests\Unit\CA;

use PHPUnit\Framework\TestCase;
use Modules\CA\Services\DeadlineService;
use Carbon\Carbon;

class DeadlineServiceTest extends TestCase
{
    private DeadlineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DeadlineService();
    }

    public function test_calculate_next_due_date_weekly()
    {
        // Set fixed date: 2026-06-12 (Friday)
        $now = Carbon::create(2026, 6, 12);
        
        $config = ['days' => ['Monday', 'Wednesday']];
        $result = $this->service->calculateNextDueDate('weekly', $config, $now);
        
        // Next Monday after 2026-06-12 is 2026-06-15
        $this->assertEquals('2026-06-15', $result->toDateString());
    }

    public function test_calculate_next_due_date_monthly()
    {
        $now = Carbon::create(2026, 6, 12);
        
        // Target is before current day
        $config = ['day_of_month' => 5];
        $result = $this->service->calculateNextDueDate('monthly', $config, $now);
        $this->assertEquals('2026-07-05', $result->toDateString());

        // Target is after current day
        $config2 = ['day_of_month' => 20];
        $result2 = $this->service->calculateNextDueDate('monthly', $config2, $now);
        $this->assertEquals('2026-06-20', $result2->toDateString());

        // Target doesn't exist (e.g. 31st in Feb)
        $nowFeb = Carbon::create(2026, 2, 1);
        $config3 = ['day_of_month' => 31];
        $result3 = $this->service->calculateNextDueDate('monthly', $config3, $nowFeb);
        // Feb 2026 has 28 days
        $this->assertEquals('2026-02-28', $result3->toDateString());
    }

    public function test_calculate_next_due_date_quarterly_calendar()
    {
        // 2026-06-12 (Q2 ends Jun 30)
        $now = Carbon::create(2026, 6, 12);
        
        $config = ['quarter_type' => 'calendar', 'due_days_after_quarter_end' => 15];
        $result = $this->service->calculateNextDueDate('quarterly', $config, $now);
        // End of Q2 is Jun 30. +15 days = Jul 15
        $this->assertEquals('2026-07-15', $result->toDateString());

        // If today is Jul 16, next calendar quarter ends Sep 30. +15 days = Oct 15
        $now2 = Carbon::create(2026, 7, 16);
        $result2 = $this->service->calculateNextDueDate('quarterly', $config, $now2);
        $this->assertEquals('2026-10-15', $result2->toDateString());
    }

    public function test_calculate_next_due_date_quarterly_financial()
    {
        // 2026-06-12
        // India FY Q1 ends Jun 30.
        $now = Carbon::create(2026, 6, 12);
        
        $config = ['quarter_type' => 'financial', 'due_days_after_quarter_end' => 20];
        $result = $this->service->calculateNextDueDate('quarterly', $config, $now);
        // End of Q1 is Jun 30. +20 days = Jul 20
        $this->assertEquals('2026-07-20', $result->toDateString());
    }

    public function test_calculate_next_due_date_yearly()
    {
        $now = Carbon::create(2026, 6, 12);
        
        $config = ['month' => 9, 'day' => 30];
        $result = $this->service->calculateNextDueDate('yearly', $config, $now);
        $this->assertEquals('2026-09-30', $result->toDateString());

        // If past the date, it goes to next year
        $now2 = Carbon::create(2026, 10, 1);
        $result2 = $this->service->calculateNextDueDate('yearly', $config, $now2);
        $this->assertEquals('2027-09-30', $result2->toDateString());
    }

    public function test_calculate_next_due_date_custom()
    {
        $now = Carbon::create(2026, 6, 12);
        
        $config = [
            'interval' => 3, 
            'unit' => 'weeks',
            'start_date' => '2026-05-01'
        ];
        $result = $this->service->calculateNextDueDate('custom', $config, $now);
        $this->assertEquals('2026-06-12', $result->toDateString());
    }

    public function test_calculate_next_due_date_daily()
    {
        // Set fixed date/time: 2026-06-12 12:00:00
        $now = Carbon::create(2026, 6, 12, 12, 0, 0);

        // Target time is 14:00 (in the future today)
        $configFuture = ['time' => '14:00'];
        $resultFuture = $this->service->calculateNextDueDate('daily', $configFuture, $now);
        $this->assertEquals('2026-06-12 14:00:00', $resultFuture->toDateTimeString());

        // Target time is 09:00 (in the past today)
        $configPast = ['time' => '09:00'];
        $resultPast = $this->service->calculateNextDueDate('daily', $configPast, $now);
        $this->assertEquals('2026-06-13 09:00:00', $resultPast->toDateTimeString());
    }

    public function test_calculate_next_due_date_for_requirement_multiple()
    {
        // 2026-06-12 (Friday)
        $now = Carbon::create(2026, 6, 12, 12, 0, 0);

        $config = [
            'schedules' => [
                [
                    'frequency' => 'monthly',
                    'config' => ['day_of_month' => 1] // next: 2026-07-01
                ],
                [
                    'frequency' => 'monthly',
                    'config' => ['day_of_month' => 15] // next: 2026-06-15
                ]
            ]
        ];

        $result = $this->service->calculateNextDueDateForRequirement('multiple', $config, $now);
        // The earliest next due date between 2026-07-01 and 2026-06-15 is 2026-06-15
        $this->assertEquals('2026-06-15', $result->toDateString());
    }
}
