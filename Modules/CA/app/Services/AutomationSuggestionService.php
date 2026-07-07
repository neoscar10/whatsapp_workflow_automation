<?php

namespace Modules\CA\Services;

use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CAAutomationLibrary;
use Illuminate\Support\Collection;

class AutomationSuggestionService
{
    public function __construct(
        private AutomationLibraryService $libraryService
    ) {}

    /**
     * Generate automation suggestions for a client, grouped by frequency.
     *
     * @param int $clientId
     * @return array<array{
     *     library: CAAutomationLibrary,
     *     frequency: string,
     *     documents: Collection,
     *     estimated_reminder_count: int,
     * }>
     */
    public function suggestForClient(int $clientId): array
    {
        // Load all recurring requirements for this client
        $recurringRequirements = CAClientComplianceRequirement::query()
            ->whereHas('clientCompliance', fn($q) => $q->where('ca_client_id', $clientId))
            ->where('is_recurring', true)
            ->with(['clientCompliance'])
            ->get();

        if ($recurringRequirements->isEmpty()) {
            return [];
        }

        // Group requirements by their primary frequency (from recurrence_frequency column or config)
        $grouped = $recurringRequirements->groupBy(function ($req) {
            return $this->resolveFrequency($req);
        })->filter(fn($docs, $frequency) => $frequency !== 'unknown');

        $suggestions = [];

        foreach ($grouped as $frequency => $documents) {
            $libraryEntry = $this->libraryService->findByFrequency($frequency);

            if (!$libraryEntry) {
                continue;
            }

            // Check if there's already an existing automation config for this client+frequency
            // to avoid duplicates in suggestions
            $suggestions[] = [
                'library'                  => $libraryEntry,
                'frequency'                => $frequency,
                'documents'                => $documents,
                'estimated_reminder_count' => $this->estimateReminderCount($frequency),
            ];
        }

        return $suggestions;
    }

    /**
     * Resolve the primary human-facing frequency from a requirement's stored data.
     */
    private function resolveFrequency(CAClientComplianceRequirement $req): string
    {
        // If recurrence_frequency is explicitly set, use it
        if (!empty($req->recurrence_frequency) && $req->recurrence_frequency !== 'multiple') {
            return $req->recurrence_frequency;
        }

        // If there's a config with schedules, take the first schedule's frequency
        $config    = $req->recurrence_config ?? [];
        $schedules = $config['schedules'] ?? [];
        if (!empty($schedules[0]['frequency'])) {
            return $schedules[0]['frequency'];
        }

        return 'unknown';
    }

    /**
     * Return a sensible estimated default reminder count for a given frequency.
     */
    private function estimateReminderCount(string $frequency): int
    {
        return match ($frequency) {
            'daily'     => 1,
            'weekly'    => 2,
            'monthly'   => 3,
            'quarterly' => 4,
            'yearly'    => 3,
            default     => 2,
        };
    }
}
