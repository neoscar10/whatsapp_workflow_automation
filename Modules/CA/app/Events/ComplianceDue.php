<?php

namespace Modules\CA\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComplianceDue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public \Modules\CA\Models\CAClientComplianceDeadline $deadline,
        public int $daysRemaining
    ) {}
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
