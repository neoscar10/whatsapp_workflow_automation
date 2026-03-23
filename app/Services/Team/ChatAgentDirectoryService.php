<?php

namespace App\Services\Team;

use App\Models\User;
use Illuminate\Support\Collection;

class ChatAgentDirectoryService
{
    /**
     * Get assignable agents for a user's company, filtered by search.
     */
    public function getAssignableAgentsForUser(User $user, array $filters = []): array
    {
        $search = $filters['search'] ?? '';

        $query = User::where('company_id', $user->company_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->get()->map(function ($agent) {
            return $this->formatAgentForAssignmentModal($agent);
        })->toArray();
    }

    /**
     * Format the agent for the assignment modal.
     */
    public function formatAgentForAssignmentModal(User $agent): array
    {
        // Mocking missing fields using safe defaults until full profiles are built out
        $roleLabel = $agent->is_company_owner ? 'Owner / Admin' : 'Support Specialist';
        $availabilityStatus = 'available'; 
        $uiStatusColor = 'bg-green-500';

        return [
            'id' => $agent->id,
            'name' => $agent->name,
            'avatar_url' => null, // Placeholder for future profile picture
            'subtitle' => "{$roleLabel} • Available",
            'availability_status' => $availabilityStatus,
            'ui_status_color' => $uiStatusColor,
            'is_assignable' => true,
            'disabled_reason' => null,
        ];
    }
}
