<?php

namespace Tests\Feature\Chat;

use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\Chat\ChatMessageService;
use App\Services\WhatsApp\WhatsAppWebhookEventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatOutboundTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Conversation $conversation;
    protected WhatsAppPhoneNumber $phoneNumber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@example.com'
        ]);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);

        $account = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'test-token',
            'waba_id' => 'test-waba-id',
        ]);

        $this->phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $account->id,
            'phone_number_id' => 'test-phone-id',
            'phone_number' => '1234567890',
            'display_name' => 'Test Number',
        ]);

        $this->conversation = Conversation::create([
            'company_id' => $this->company->id,
            'whatsapp_phone_number_id' => $this->phoneNumber->id,
            'contact_name' => 'Test Contact',
            'contact_phone' => '0987654321',
        ]);
    }

    public function test_outbound_message_calls_meta_api_and_updates_status()
    {
        Http::fake([
            '*' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => '0987654321', 'wa_id' => '0987654321']],
                'messages' => [['id' => 'meta-msg-123']]
            ], 200)
        ]);

        $service = app(ChatMessageService::class);
        $message = $service->sendTextMessage($this->user, $this->conversation->id, 'Hello from platform');

        $this->assertNotNull($message);
        $this->assertEquals('sent', $message->status);
        $this->assertEquals('meta-msg-123', $message->external_message_id);

        Http::assertSent(function ($request) {
            return $request->url() == "https://graph.facebook.com/v21.0/test-phone-id/messages" &&
                   $request['to'] == '0987654321' &&
                   $request['text']['body'] == 'Hello from platform';
        });
    }

    public function test_outbound_message_failure_updates_status_to_failed()
    {
        Http::fake([
            '*' => Http::response([
                'error' => ['message' => 'Invalid recipient']
            ], 400)
        ]);

        $service = app(ChatMessageService::class);
        $message = $service->sendTextMessage($this->user, $this->conversation->id, 'Hello from platform');

        $this->assertEquals('failed', $message->status);
        $this->assertNull($message->external_message_id);
    }

    public function test_webhook_status_update_updates_local_message()
    {
        $message = ConversationMessage::create([
            'conversation_id' => $this->conversation->id,
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => 'Test message',
            'status' => 'sent',
            'external_message_id' => 'meta-msg-123'
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => 'test-waba-id',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => ['display_phone_number' => '1234567890', 'phone_number_id' => 'test-phone-id'],
                        'statuses' => [[
                            'id' => 'meta-msg-123',
                            'status' => 'delivered',
                            'timestamp' => now()->timestamp,
                            'recipient_id' => '0987654321'
                        ]]
                    ]
                ]]
            ]]
        ];

        $webhookService = app(WhatsAppWebhookEventService::class);
        $webhookService->handle($payload);

        $message->refresh();
        $this->assertEquals('delivered', $message->status);
        $this->assertNotNull($message->delivered_at);
    }
}
