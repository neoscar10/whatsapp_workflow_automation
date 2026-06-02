<?php

namespace App\Livewire\SuperAdmin;

use App\Services\Platform\PlatformDashboardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class SuperAdminDashboard extends Component
{
    public array $stats = [];

    public function mount(PlatformDashboardService $dashboardService)
    {
        $this->stats = [
            'companies_count' => $dashboardService->getCompanyCount(),
            'users_count' => $dashboardService->getUserCount(),
            'wallet_transactions' => $dashboardService->getWalletTransactionCount(),
            'whatsapp_connections' => $dashboardService->getWhatsAppConnectionCount(),
            'automations' => $dashboardService->getAutomationCount(),
            'campaigns' => $dashboardService->getCampaignCount(),
            'messages_sent' => $dashboardService->getMessagesSentCount(),
        ];
    }

    public function render()
    {
        return view('livewire.super-admin.dashboard')
            ->layout('layouts.super-admin', [
                'title' => 'Super Admin Dashboard',
                'activeNav' => 'dashboard',
            ]);
    }
}
