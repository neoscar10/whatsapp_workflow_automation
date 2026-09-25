<?php

namespace Tests\Feature\Chat;

use App\Models\Company;
use App\Models\Contact\Contact;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\Chat\ChatConversationResolverService;
use App\Services\WhatsApp\WhatsAppOutboundMessageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StickerMessageTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $user;
    protected WhatsAppAccount $account;
    protected WhatsAppPhoneNumber $phoneNumber;
    protected Contact $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Sticker Test Company',
            'slug' => 'sticker-test-' . uniqid(),
            'primary_email' => 'sticker_company@example.com',
            'demo_credits' => 1000.00,
            'status' => 'demo',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'sticker_user@example.com',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
            'is_company_owner' => true,
        ]);

        $this->account = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'test_access_token',
            'waba_id' => '123456789',
            'business_id' => '987654321',
            'connection_status' => 'connected',
        ]);

        $this->phoneNumber = WhatsAppPhoneNumber::create([
            'company_id' => $this->company->id,
            'whatsapp_account_id' => $this->account->id,
            'phone_number_id' => 'PN_123456',
            'display_name' => 'Test Phone',
            'display_phone_number' => '+1234567890',
            'phone_number' => '+1234567890',
            'status' => 'active',
        ]);

        $this->contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'John Sticker',
            'phone' => '+19876543210',
        ]);
    }

    public function test_inbound_sticker_message_is_processed_and_saved()
    {
        Http::fake([
            'https://graph.facebook.com/v21.0/MEDIA_STICKER_123*' => Http::response([
                'url' => 'https://graph.facebook.com/v21.0/download_sticker_binary',
                'mime_type' => 'image/webp',
                'file_size' => 1234,
                'id' => 'MEDIA_STICKER_123'
            ], 200),
            'https://graph.facebook.com/v21.0/download_sticker_binary*' => Http::response('RIFF_WEBP_BINARY_DATA', 200),
        ]);

        $resolver = app(ChatConversationResolverService::class);

        $messagePayload = [
            'from' => '19876543210',
            'id' => 'wamid.sticker_123',
            'timestamp' => time(),
            'type' => 'sticker',
            'sticker' => [
                'id' => 'MEDIA_STICKER_123',
                'mime_type' => 'image/webp',
                'sha256' => 'mock_sha256',
                'animated' => false,
            ]
        ];

        $savedMessage = $resolver->resolveAndProcessInboundMessage($this->phoneNumber, $messagePayload, [
            'profile' => ['name' => 'John Sticker'],
            'wa_id' => '19876543210',
        ]);

        $this->assertNotNull($savedMessage);
        $this->assertEquals('sticker', $savedMessage->message_type);
        $this->assertEquals('Sticker', $savedMessage->body);
        $this->assertNotNull($savedMessage->media_url);
        $this->assertStringContainsString('webp', $savedMessage->media_url);
    }

    public function test_outbound_sticker_message_dispatches_to_meta()
    {
        Http::fake([
            '*' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => '19876543210', 'wa_id' => '19876543210']],
                'messages' => [['id' => 'wamid.outbound_sticker_999']]
            ], 200)
        ]);

        $conversation = Conversation::create([
            'company_id' => $this->company->id,
            'whatsapp_phone_number_id' => $this->phoneNumber->id,
            'contact_id' => $this->contact->id,
            'contact_phone' => $this->contact->phone,
            'contact_name' => $this->contact->name,
            'status' => 'open',
        ]);

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'direction' => 'outbound',
            'message_type' => 'sticker',
            'body' => 'Sticker',
            'media_url' => 'chat_media/outbound_sticker.webp',
            'media_meta' => [
                'media_id' => 'OUTBOUND_STICKER_MEDIA_ID',
                'mime_type' => 'image/webp',
            ],
            'status' => 'pending',
        ]);

        $outboundService = app(WhatsAppOutboundMessageService::class);
        $success = $outboundService->sendConversationMessage($message);

        $this->assertTrue($success);
        $this->assertEquals('sent', $message->fresh()->status);
        $this->assertNotEmpty($message->fresh()->external_message_id);
    }
}
