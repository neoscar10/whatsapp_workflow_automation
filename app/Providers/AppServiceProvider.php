<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Payment\PaymentService::class, function ($app) {
            return new \App\Services\Payment\PaymentService($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\AutomationEventSubscriber::class);

        // Register core sidebar links
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'dashboard',
            'activePattern' => 'dashboard',
            'group' => 'core',
            'order' => 10,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Chats',
            'route' => 'chats.index',
            'icon' => 'chat',
            'activePattern' => 'chats',
            'group' => 'core',
            'order' => 20,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Contacts',
            'route' => 'contacts.index',
            'icon' => 'group',
            'activePattern' => 'contacts',
            'group' => 'core',
            'order' => 30,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Audience Manager',
            'route' => 'contacts.audiences',
            'icon' => 'groups_2',
            'activePattern' => 'contacts.audiences',
            'group' => 'core',
            'order' => 40,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Campaigns',
            'route' => 'campaigns.index',
            'icon' => 'campaign',
            'activePattern' => 'campaigns',
            'group' => 'core',
            'order' => 50,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Company Profile',
            'route' => 'company.profile',
            'icon' => 'business',
            'activePattern' => 'company-profile',
            'group' => 'core',
            'order' => 60,
            'hasVerificationBadge' => true,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Templates',
            'route' => 'whatsapp.templates.index',
            'icon' => 'description',
            'activePattern' => 'whatsapp-templates',
            'group' => 'core',
            'order' => 70,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Automations',
            'route' => 'automations.index',
            'icon' => 'auto_awesome',
            'activePattern' => 'automations.*',
            'group' => 'core',
            'order' => 80,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'WhatsApp Setup',
            'route' => 'whatsapp.setup.phone-numbers',
            'icon' => 'settings_suggest',
            'activePattern' => 'whatsapp-setup',
            'group' => 'core',
            'order' => 90,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'Webhooks',
            'route' => 'webhooks.index',
            'icon' => 'webhook',
            'activePattern' => 'webhooks.*',
            'group' => 'core',
            'order' => 95,
        ]);
        \App\Support\Sidebar\SidebarRegistry::register([
            'title' => 'My Wallet',
            'route' => 'wallet.index',
            'icon' => 'account_balance_wallet',
            'activePattern' => 'wallet',
            'group' => 'core',
            'order' => 100,
            'hasLowBalanceBadge' => true,
        ]);
    }
}
