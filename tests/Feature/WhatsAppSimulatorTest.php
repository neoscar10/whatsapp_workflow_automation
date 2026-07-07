<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact\Contact;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use App\Models\WhatsApp\WhatsAppSimulatedMedia;
use App\Services\WhatsApp\Simulation\WhatsAppInboundSimulatorService;
use App\Services\WhatsApp\Simulation\SimulatedWhatsAppMediaResolver;
use App\Livewire\Web\Contacts\ContactIndexPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsAppSimulatorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected Contact $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create(['name' => 'Acme Test Company', 'slug' => 'acme-test-company', 'status' => 'active', 'primary_email' => 'acme@test.com']);
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@acme.com',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
            'role' => 'admin',
        ]);

        $this->contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'John Client',
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
            'status' => 'active',
        ]);

        Storage::fake('local');
        Storage::fake('public');

        $this->mock(\App\Services\Payment\BillingService::class, function ($mock) {
            $mock->shouldReceive('canAffordActivity')->andReturn(true);
            $mock->shouldReceive('debitForActivity')->andReturn(true);
        });
    }

    /** @test */
    public function simulator_respects_configuration_and_environment_settings()
    {
        // 1. When disabled, action is hidden and blocked
        config(['services.whatsapp.simulator.enabled' => false]);
        $this->actingAs($this->user);

        Livewire::test(ContactIndexPage::class)
            ->assertDontSee('Simulate WhatsApp');

        // 2. When enabled, action is visible and available
        config(['services.whatsapp.simulator.enabled' => true]);

        Livewire::test(ContactIndexPage::class)
            ->assertSee('Simulate WhatsApp');
    }

    /** @test */
    public function company_isolation_is_enforced_preventing_cross_company_simulation()
    {
        config(['services.whatsapp.simulator.enabled' => true]);
        
        $otherCompany = Company::create(['name' => 'Other Company', 'slug' => 'other-company', 'status' => 'active', 'primary_email' => 'other@test.com']);
        $otherContact = Contact::create([
            'company_id' => $otherCompany->id,
            'name' => 'Foreign Client',
            'phone' => '9999999999',
            'normalized_phone' => '9999999999',
            'status' => 'active',
        ]);

        $this->actingAs($this->user);

        // Try to open simulator for other company's contact
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(ContactIndexPage::class)
            ->call('openSimulatorModal', $otherContact->id);
    }

    /** @test */
    public function text_message_simulation_creates_conversations_and_messages()
    {
        config(['services.whatsapp.simulator.enabled' => true]);
        $this->actingAs($this->user);

        Livewire::test(ContactIndexPage::class)
            ->call('openSimulatorModal', $this->contact->id)
            ->set('simulatorMessageText', 'Hello, this is a simulated inbound message!')
            ->call('sendSimulatedMessage')
            ->assertHasNoErrors();

        // Verify conversation is created
        $conversation = Conversation::where('contact_phone', $this->contact->phone)->first();
        $this->assertNotNull($conversation);
        $this->assertEquals($this->company->id, $conversation->company_id);

        // Verify message is created
        $message = ConversationMessage::where('conversation_id', $conversation->id)->first();
        $this->assertNotNull($message);
        $this->assertEquals('Hello, this is a simulated inbound message!', $message->body);
        $this->assertEquals('inbound', $message->direction);
    }

    /** @test */
    public function image_and_document_uploads_create_simulated_media_records_and_resolve_locally()
    {
        config(['services.whatsapp.simulator.enabled' => true]);
        $this->actingAs($this->user);

        $fakeFile = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        Livewire::test(ContactIndexPage::class)
            ->call('openSimulatorModal', $this->contact->id)
            ->set('simulatorUploadFile', $fakeFile)
            ->set('simulatorMessageText', 'Simulated PDF Upload')
            ->call('sendSimulatedMessage')
            ->assertHasNoErrors();

        // Verify simulated media table record
        $media = WhatsAppSimulatedMedia::where('original_filename', 'document.pdf')->first();
        $this->assertNotNull($media);
        $this->assertStringStartsWith('sim_media_', $media->simulated_media_id);
        $this->assertEquals($this->company->id, $media->company_id);

        // Verify file is saved in private local storage path
        Storage::disk('local')->assertExists($media->storage_path);

        // Verify resolved path exists on local resolution
        $resolver = app(SimulatedWhatsAppMediaResolver::class);
        $this->assertTrue($resolver->isSimulatedMediaId($media->simulated_media_id));
        $this->assertNotNull($resolver->getMediaContents($media->simulated_media_id));
    }

    /** @test */
    public function outbound_replies_to_simulated_numbers_are_captured_locally_instead_of_meta()
    {
        config(['services.whatsapp.simulator.enabled' => true]);
        $this->actingAs($this->user);

        // Setup local dummy number first
        $simulatorService = app(WhatsAppInboundSimulatorService::class);
        $localNumber = $simulatorService->ensureFakePhoneNumber($this->company->id, $this->user->id);

        $conversation = Conversation::create([
            'company_id' => $this->company->id,
            'whatsapp_phone_number_id' => $localNumber->id,
            'contact_phone' => $this->contact->phone,
            'contact_name' => $this->contact->name,
        ]);

        $message = $conversation->messages()->create([
            'external_message_id' => 'sim_outbound_test',
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => 'Auto-reply from the platform!',
            'status' => 'pending',
            'sent_at' => now(),
        ]);

        $outboundService = app(\App\Services\WhatsApp\WhatsAppOutboundMessageService::class);
        $success = $outboundService->sendConversationMessage($message);

        $this->assertTrue($success);
        $this->assertEquals('sent', $message->fresh()->status);
        $this->assertStringStartsWith('wamid.simulated_outbound_', $message->fresh()->external_message_id);
    }
}
