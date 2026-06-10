<?php

namespace Modules\CA\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\CA\Events\ComplianceDue;
use Modules\CA\Events\ComplianceOverdue;
use Modules\CA\Events\ComplianceCompleted;
use Modules\CA\Events\DocumentUploaded;
use Modules\CA\Events\DocumentApproved;
use Modules\CA\Events\DocumentRejected;
use Modules\CA\Events\RequirementCompleted;
use Modules\CA\Listeners\TriggerCAAutomation;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        ComplianceDue::class => [
            TriggerCAAutomation::class,
        ],
        ComplianceOverdue::class => [
            TriggerCAAutomation::class,
        ],
        ComplianceCompleted::class => [
            TriggerCAAutomation::class,
            \Modules\CA\Listeners\RolloverRecurringCompliance::class,
        ],
        DocumentUploaded::class => [
            TriggerCAAutomation::class,
        ],
        DocumentApproved::class => [
            TriggerCAAutomation::class,
        ],
        DocumentRejected::class => [
            TriggerCAAutomation::class,
        ],
        RequirementCompleted::class => [
            TriggerCAAutomation::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
