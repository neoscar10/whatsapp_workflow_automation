<?php

namespace App\Services\Webhooks;

use App\Jobs\Webhooks\DispatchCompanyWebhookJob;
use App\Models\Company;
use App\Models\Webhooks\CompanyWebhook;

class CompanyWebhookDispatcherService
{
    /**
     * Dispatch webhooks for a company matching an event type.
     *
     * @param Company $company
     * @param string $eventType
     * @param array $payload
     * @return void
     */
    public function dispatch(Company $company, string $eventType, array $payload): void
    {
        $webhooks = CompanyWebhook::where('company_id', $company->id)
            ->where('is_active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            $subscribedEvents = $webhook->events ?? [];

            if (in_array($eventType, $subscribedEvents, true) || in_array('*', $subscribedEvents, true)) {
                DispatchCompanyWebhookJob::dispatch($webhook->id, $eventType, $payload);
            }
        }
    }
}
