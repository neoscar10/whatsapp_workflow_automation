<?php

namespace App\Jobs\Notifications;

use App\Models\UserDeviceToken;
use App\Services\Notifications\MobilePushNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMobilePushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $companyId,
        public int $conversationId,
        public int $messageId,
        public string $senderName,
        public string $previewText
    ) {}

    public function handle(MobilePushNotificationService $pushService): void
    {
        $deviceTokens = UserDeviceToken::where('company_id', $this->companyId)
            ->whereNotNull('device_token')
            ->pluck('device_token');

        if ($deviceTokens->isEmpty()) {
            return;
        }

        $title = "💬 New WhatsApp Message: " . ($this->senderName ?: 'Contact');
        $body = mb_substr($this->previewText, 0, 150);

        $payloadData = [
            'type' => 'whatsapp_inbound_message',
            'company_id' => (string) $this->companyId,
            'conversation_id' => (string) $this->conversationId,
            'message_id' => (string) $this->messageId,
            'click_action' => 'OPEN_CONVERSATION',
        ];

        $pushService->sendPushToTokens($deviceTokens, $title, $body, $payloadData);
    }
}
