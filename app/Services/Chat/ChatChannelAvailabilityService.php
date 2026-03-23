<?php

namespace App\Services\Chat;

use App\Models\User;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Illuminate\Support\Collection;

class ChatChannelAvailabilityService
{
    /**
     * Get all active and usable WhatsApp numbers for the user's company.
     *
     * @param User $user
     * @return Collection
     */
    public function getAvailableWhatsAppNumbersForUser(User $user): Collection
    {
        return WhatsAppPhoneNumber::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->whereNotNull('phone_number_id')
            ->with('account')
            ->get()
            ->filter(function ($number) {
                // A number is only truly usable if its parent account is connected
                return $number->account && $number->account->connection_status === 'connected';
            })
            ->values();
    }

    /**
     * Get the default WhatsApp number to use for new conversations.
     *
     * @param User $user
     * @return WhatsAppPhoneNumber|null
     */
    public function getDefaultWhatsAppNumberForUser(User $user): ?WhatsAppPhoneNumber
    {
        return $this->getAvailableWhatsAppNumbersForUser($user)->first();
    }

    /**
     * Check if a specific phone number is eligible for chat.
     *
     * @param WhatsAppPhoneNumber $phoneNumber
     * @return bool
     */
    public function isNumberChatEligible(WhatsAppPhoneNumber $phoneNumber): bool
    {
        return $phoneNumber->status === 'active' 
            && $phoneNumber->phone_number_id 
            && $phoneNumber->account 
            && $phoneNumber->account->connection_status === 'connected';
    }
}
