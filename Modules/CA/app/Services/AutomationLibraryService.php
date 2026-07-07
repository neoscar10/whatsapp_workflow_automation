<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAAutomationLibrary;
use Illuminate\Support\Collection;

class AutomationLibraryService
{
    /**
     * Return the full active automation library catalogue.
     */
    public function getCatalogue(): Collection
    {
        return CAAutomationLibrary::active()->get();
    }

    /**
     * Find a library entry by frequency slug (e.g. 'monthly', 'weekly').
     */
    public function findByFrequency(string $frequency): ?CAAutomationLibrary
    {
        return CAAutomationLibrary::where('frequency', $frequency)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Ensure the base automation library catalogue exists.
     * Safe to call repeatedly (uses firstOrCreate).
     */
    public function seedDefaultLibrary(): void
    {
        $defaults = [
            [
                'name'        => 'Monthly Document Collection',
                'slug'        => 'monthly-document-collection',
                'frequency'   => 'monthly',
                'description' => 'Automate collection of monthly recurring documents.',
                'icon'        => 'calendar_month',
                'color'       => 'teal',
            ],
            [
                'name'        => 'Weekly Document Collection',
                'slug'        => 'weekly-document-collection',
                'frequency'   => 'weekly',
                'description' => 'Automate collection of weekly recurring documents such as attendance sheets.',
                'icon'        => 'calendar_view_week',
                'color'       => 'blue',
            ],
            [
                'name'        => 'Quarterly Document Collection',
                'slug'        => 'quarterly-document-collection',
                'frequency'   => 'quarterly',
                'description' => 'Automate collection of quarterly recurring documents such as GST returns and TDS filings.',
                'icon'        => 'date_range',
                'color'       => 'indigo',
            ],
            [
                'name'        => 'Annual Document Collection',
                'slug'        => 'annual-document-collection',
                'frequency'   => 'yearly',
                'description' => 'Automate collection of yearly recurring documents such as ITR and audit reports.',
                'icon'        => 'event_note',
                'color'       => 'violet',
            ],
            [
                'name'        => 'Daily Compliance Reminder',
                'slug'        => 'daily-compliance-reminder',
                'frequency'   => 'daily',
                'description' => 'Daily follow-up reminders for pending compliance actions.',
                'icon'        => 'notification_important',
                'color'       => 'amber',
            ],
        ];

        foreach ($defaults as $item) {
            CAAutomationLibrary::firstOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, ['status' => 'active', 'ai_prompt_version' => '1.0'])
            );
        }
    }
}
