<?php

namespace Tests\Feature;

use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\WhatsApp\WhatsAppWebhookEventService;
use App\Events\Chat\ChatMessageReceived;
use App\Events\Chat\ChatConversationUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ChatDeliveryStatusTest extends TestCase
{
    use RefreshDatabase;

    private function setupFullContext()
    {
        $company = Company::forceCreate(['name' => 'Test Co', 'slug' => 'test-co', 'primary_email' => 'test@test.com']);
        $user = User::forceCreate(['name' => 'Test User', 'email' => 'test@test.com', 'password' => 'pwd', 'company_id' => $company->id]);
        $account = WhatsAppAccount::forceCreate(['company_id' => $company->id, 'waba_id' => 'waba123', 'access_token' => 'at']);
        $phoneNumber = WhatsAppPhoneNumber::forceCreate([
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'phone_number_id' => 'phone123',
            'display_name' => 'Test Number',
            'phone_number' => '12345'
        ]);
        $conversation = Conversation::create([
            'company_id' => $company->id,
            'whatsapp_phone_number_id' => $phoneNumber->id,
            'contact_phone' => '987654321',
            'contact_name' => 'Customer'
        ]);

        return [$company, $user, $account, $phoneNumber, $conversation];
    }

    public function test_webhook_status_delivered_updates_message()
    {
        Event::fake();
        [$company, $user, $account, $phoneNumber, $conversation] = $this->setupFullContext();

        $message = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => 'Hello',
            'status' => 'sent',
            'external_message_id' => 'wam_123'
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => 'waba123',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => ['display_phone_number' => '12345', 'phone_number_id' => 'phone123'],
                        'statuses' => [[
                            'id' => 'wam_123',
                            'status' => 'delivered',
                            'timestamp' => 1711484400,
                            'recipient_id' => '987654321'
                        ]]
                    ]
                ]]
            ]]
        ];

        app(WhatsAppWebhookEventService::class)->handle($payload);

        $message->refresh();
        $this->assertEquals('delivered', $message->status);
        $this->assertNotNull($message->delivered_at);
        $this->assertEquals(1711484400, $message->delivered_at->timestamp);

        Event::assertDispatched(ChatMessageReceived::class);
        Event::assertDispatched(ChatConversationUpdated::class);
    }

    public function test_webhook_status_read_updates_message()
    {
        Event::fake();
        [$company, $user, $account, $phoneNumber, $conversation] = $this->setupFullContext();

        $message = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => 'Hello',
            'status' => 'delivered',
            'external_message_id' => 'wam_123',
            'delivered_at' => now()->subMinute()
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => 'waba123',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => ['phone_number_id' => 'phone123'],
                        'statuses' => [[
                            'id' => 'wam_123',
                            'status' => 'read',
                            'timestamp' => 1711484500,
                            'recipient_id' => '987654321'
                        ]]
                    ]
                ]]
            ]]
        ];

        app(WhatsAppWebhookEventService::class)->handle($payload);

        $message->refresh();
        $this->assertEquals('read', $message->status);
        $this->assertNotNull($message->read_at);
        $this->assertEquals(1711484500, $message->read_at->timestamp);
    }

    public function test_status_progression_is_respected()
    {
        [$company, $user, $account, $phoneNumber, $conversation] = $this->setupFullContext();

        $message = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => 'Hello',
            'status' => 'read',
            'external_message_id' => 'wam_123',
            'read_at' => now()->subMinute()
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => 'waba123',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => ['phone_number_id' => 'phone123'],
                        'statuses' => [[
                            'id' => 'wam_123',
                            'status' => 'delivered', // Late/duplicate webhook
                            'timestamp' => 1711484400,
                            'recipient_id' => '987654321'
                        ]]
                    ]
                ]]
            ]]
        ];

        app(WhatsAppWebhookEventService::class)->handle($payload);

        $message->refresh();
        $this->assertEquals('read', $message->status); // Should stay 'read'
    }

    public function test_webhook_status_failed_stores_details()
    {
        [$company, $user, $account, $phoneNumber, $conversation] = $this->setupFullContext();

        $message = $conversation->messages()->create([
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => 'Hello',
            'status' => 'sent',
            'external_message_id' => 'wam_err'
        ]);

        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => 'waba123',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => ['phone_number_id' => 'phone123'],
                        'statuses' => [[
                            'id' => 'wam_err',
                            'status' => 'failed',
                            'timestamp' => 1711484600,
                            'recipient_id' => '987654321',
                            'errors' => [[
                                'code' => 131047,
                                'title' => 'Message failed to send',
                                'message' => 'Re-engagement message'
                            ]]
                        ]]
                    ]
                ]]
            ]]
        ];

        app(WhatsAppWebhookEventService::class)->handle($payload);

        $message->refresh();
        $this->assertEquals('failed', $message->status);
        $this->assertEquals('131047', $message->failure_code);
        $this->assertEquals('Re-engagement message', $message->failure_message);
        $this->assertNotNull($message->failed_at);
    }
}
