<?php

namespace App\Services\Platform;

use App\Models\Company;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\AutomationFlow;
use App\Models\Campaign\Campaign;
use App\Models\Chat\ConversationMessage;

class PlatformDashboardService
{
    public function getCompanyCount(): int
    {
        return Company::count();
    }

    public function getUserCount(): int
    {
        return User::count();
    }

    public function getWalletTransactionCount(): int
    {
        return WalletTransaction::count();
    }

    public function getWhatsAppConnectionCount(): int
    {
        return WhatsAppPhoneNumber::count();
    }

    public function getAutomationCount(): int
    {
        return AutomationFlow::count();
    }

    public function getCampaignCount(): int
    {
        return Campaign::count();
    }

    public function getMessagesSentCount(): int
    {
        return ConversationMessage::where('direction', 'outbound')->count();
    }
}
