<?php

namespace Modules\CA\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CACompliance;
use Modules\CA\Models\CAServiceCategory;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Models\CAClientComplianceRequirement;
use Modules\CA\Models\CADocument;
use Modules\CA\Models\CANotification;
use Modules\CA\Services\CAInboundDocumentIntakeService;
use Modules\CA\Services\CADocumentMatchingService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;
use App\Events\Chat\ChatMessageReceived;
use Exception;

class InboundDocumentIntakeTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User $user;
    private CAClient $client;
    private WhatsAppAccount $wabaAccount;
    private WhatsAppPhoneNumber $wabaPhone;
    private Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');

        $this->company = Company::create([
            'name' => 'CA Firm',
            'slug' => 'ca-firm',
            'primary_email' => 'firm@ca.com',
            'status' => 'demo',
            'demo_credits' => '100.0000',
        ]);

        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'is_company_owner' => true,
        ]);

        app(\App\Services\Wallet\WalletService::class)->getOrCreateWallet($this->user);

        $this->client = CAClient::create([
            'company_id' => $this->company->id,
            'client_name' => 'John Doe Traders',
            'email' => 'john@doe.com',
            'phone' => '919000000000',
            'status' => 'active',
        ]);

        $this->wabaAccount = WhatsAppAccount::create([
            'company_id' => $this->company->id,
            'access_token' => 'token',
            'waba_id' => 'waba_id',
            'connection_status' => 'connected',
        ]);

        $this->wabaPhone = WhatsAppPhoneNumber::create([
            'whatsapp_account_id' => $this->wabaAccount->id,
            'company_id' => $this->company->id,
            'phone_number_id' => '12345',
            'phone_number' => '917777777777',
            'verified_name' => 'CA Firm Outbound',
            'display_name' => 'CA Firm Outbound',
            'status' => 'verified',
        ]);

        $this->conversation = Conversation::create([
            'company_id' => $this->company->id,
            'whatsapp_phone_number_id' => $this->wabaPhone->id,
            'contact_phone' => '919000000000',
            'contact_name' => 'John Doe',
        ]);
    }

    public function test_inbound_document_intake_auto_matches_and_provisions_private_file()
    {
        // 1. Setup pending requirement
        $category = CAServiceCategory::create(['name' => 'Tax', 'slug' => 'tax']);
        $compliance = CACompliance::create([
            'ca_service_category_id' => $category->id,
            'name' => 'GST Filing',
            'slug' => 'gst-filing',
            'is_recurring' => true,
        ]);
        $clientCompliance = CAClientCompliance::create([
            'ca_client_id' => $this->client->id,
            'ca_compliance_id' => $compliance->id,
            'status' => 'active',
        ]);
        $requirement = CAClientComplianceRequirement::create([
            'ca_client_compliance_id' => $clientCompliance->id,
            'name' => 'GST Registration Certificate',
            'requirement_type' => 'document',
            'input_type' => 'file',
            'is_recurring' => false,
            'next_due_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        // Create public chat media source file
        $publicPath = 'chat_media/mock_gst.pdf';
        Storage::disk('public')->put($publicPath, 'fake pdf contents');

        // Create Inbound Message representation
        $message = ConversationMessage::create([
            'conversation_id' => $this->conversation->id,
            'external_message_id' => 'msg_111',
            'direction' => 'inbound',
            'message_type' => 'document',
            'body' => 'GST Certificate',
            'status' => 'received',
            'media_url' => $publicPath,
            'media_meta' => [
                'local_path' => $publicPath,
                'mime_type'  => 'application/pdf',
                'filename'   => 'gst_doc.pdf',
            ],
        ]);

        // Mock AI output inside intake process
        $this->mockAIClassification([
            'detected_document_type' => 'gst_certificate',
            'detected_document_name' => 'GST Registration Certificate',
            'confidence' => 0.95,
            'reason' => 'Contains GSTIN and Indian government seal.',
        ]);

        // Trigger intake listener manually (matching ChatMessageReceived trigger)
        event(new ChatMessageReceived($message));

        // Assert requirement status updated to under_review
        $this->assertEquals('under_review', $requirement->fresh()->status);

        // Assert CADocument created
        $doc = CADocument::where('ca_client_id', $this->client->id)->first();
        $this->assertNotNull($doc);
        $this->assertEquals('local', $doc->storage_disk);

        // Assert file copied to private scoped disk directory
        $this->assertTrue(Storage::disk('local')->exists($doc->storage_path));
        $this->assertStringContainsString("ca_documents/{$this->company->id}/{$this->client->id}/", $doc->storage_path);

        // Assert CA Notification generated
        $notification = CANotification::where('ca_client_id', $this->client->id)->first();
        $this->assertNotNull($notification);
        $this->assertEquals('document_matched', $notification->type);

        // Assert outbox has session response message
        $reply = ConversationMessage::where('direction', 'outbound')->orderByDesc('id')->first();
        $this->assertNotNull($reply);
        $this->assertStringContainsString("Document Received: *GST Registration Certificate*", $reply->body);
    }

    public function test_bypass_conditions_when_no_pending_requirements()
    {
        // Public attachment is saved
        $publicPath = 'chat_media/tax.pdf';
        Storage::disk('public')->put($publicPath, 'pdf content');

        $message = ConversationMessage::create([
            'conversation_id' => $this->conversation->id,
            'external_message_id' => 'msg_222',
            'direction' => 'inbound',
            'message_type' => 'document',
            'body' => 'tax.pdf',
            'status' => 'received',
            'media_url' => $publicPath,
            'media_meta' => [
                'local_path' => $publicPath,
                'mime_type'  => 'application/pdf',
                'filename'   => 'tax.pdf',
            ],
        ]);

        // Trigger
        event(new ChatMessageReceived($message));

        // Assert no CADocument and no Notifications were created (since client has zero compliance items)
        $this->assertEquals(0, CADocument::count());
        $this->assertEquals(0, CANotification::count());
    }

    public function test_unmatched_low_confidence_results_create_review_alerts()
    {
        // Setup requirement
        $category = CAServiceCategory::create(['name' => 'Tax', 'slug' => 'tax']);
        $compliance = CACompliance::create([
            'ca_service_category_id' => $category->id,
            'name' => 'GST Filing',
            'slug' => 'gst-filing',
            'is_recurring' => true,
        ]);
        $clientCompliance = CAClientCompliance::create([
            'ca_client_id' => $this->client->id,
            'ca_compliance_id' => $compliance->id,
            'status' => 'active',
        ]);
        $requirement = CAClientComplianceRequirement::create([
            'ca_client_compliance_id' => $clientCompliance->id,
            'name' => 'TDS Statement',
            'requirement_type' => 'document',
            'input_type' => 'file',
            'is_recurring' => false,
            'next_due_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $publicPath = 'chat_media/random.jpg';
        Storage::disk('public')->put($publicPath, 'jpg content');

        $message = ConversationMessage::create([
            'conversation_id' => $this->conversation->id,
            'external_message_id' => 'msg_333',
            'direction' => 'inbound',
            'message_type' => 'image',
            'body' => 'random.jpg',
            'status' => 'received',
            'media_url' => $publicPath,
            'media_meta' => [
                'local_path' => $publicPath,
                'mime_type'  => 'image/jpeg',
                'filename'   => 'random.jpg',
            ],
        ]);

        // Mock low-confidence AI output
        $this->mockAIClassification([
            'detected_document_type' => 'unknown',
            'detected_document_name' => 'Unrecognized Receipt',
            'confidence' => 0.35,
            'reason' => 'Highly blurred image.',
        ]);

        event(new ChatMessageReceived($message));

        // Assert requirement status remains pending
        $this->assertEquals('pending', $requirement->fresh()->status);

        // Assert Notification has status 'match_failed' indicating it needs human review
        $notification = CANotification::where('ca_client_id', $this->client->id)->first();
        $this->assertNotNull($notification);
        $this->assertEquals('match_failed', $notification->type);

        // Assert response tells client the document couldn't be auto-matched
        $reply = ConversationMessage::where('direction', 'outbound')->orderByDesc('id')->first();
        $this->assertStringContainsString("could not confidently classify", $reply->body);
    }

    /**
     * Helper to mock AI service response.
     */
    private function mockAIClassification(array $result): void
    {
        $mockProvider = $this->createMock(\Modules\CA\Services\AI\Providers\GeminiProvider::class);
        $mockProvider->method('generateStructuredResponse')->willReturn($result);

        $mockManager = $this->createMock(\Modules\CA\Services\AI\Managers\AIManager::class);
        $mockManager->method('provider')->willReturn($mockProvider);

        $this->app->instance(\Modules\CA\Services\AI\Managers\AIManager::class, $mockManager);
    }
}
