<?php

namespace Tests\Feature\Api\V1\Chat;

use App\Models\Company;
use App\Models\User;
use App\Models\Chat\Conversation;
use App\Models\Chat\ConversationMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'status' => 'active',
            'primary_email' => 'test@company.com',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_list_chats()
    {
        $response = $this->getJson(route('api.v1.chats.index'));
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_own_company_chats()
    {
        Conversation::create([
            'company_id' => $this->company->id,
            'contact_name' => 'John Doe',
            'contact_phone' => '+1234567890',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $otherCompany = Company::create([
            'name' => 'Other', 
            'slug' => 'other', 
            'status' => 'active', 
            'primary_email' => 'other@company.com'
        ]);
        Conversation::create([
            'company_id' => $otherCompany->id,
            'contact_name' => 'Jane Smith',
            'contact_phone' => '+0987654321',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.chats.index'));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.contact_name', 'John Doe');
    }

    public function test_user_can_show_own_conversation()
    {
        $conversation = Conversation::create([
            'company_id' => $this->company->id,
            'contact_name' => 'John Doe',
            'contact_phone' => '+1234567890',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.chats.show', ['conversation' => $conversation->id]));

        $response->assertStatus(200)
            ->assertJsonPath('data.contact_name', 'John Doe');
    }

    public function test_user_cannot_show_another_company_conversation()
    {
        $otherCompany = Company::create([
            'name' => 'Other', 
            'slug' => 'other', 
            'status' => 'active', 
            'primary_email' => 'other@company.com'
        ]);
        $conversation = Conversation::create([
            'company_id' => $otherCompany->id,
            'contact_name' => 'Jane Smith',
            'contact_phone' => '+0987654321',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.chats.show', ['conversation' => $conversation->id]));

        $response->assertStatus(404);
    }

    public function test_user_can_list_messages_for_own_conversation()
    {
        $conversation = Conversation::create([
            'company_id' => $this->company->id,
            'contact_name' => 'John Doe',
            'contact_phone' => '+1234567890',
            'status' => 'open',
        ]);

        $conversation->messages()->create([
            'direction' => 'inbound',
            'message_type' => 'text',
            'body' => 'Hello',
            'status' => 'received',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.chats.messages.index', ['conversation' => $conversation->id]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.body', 'Hello');
    }

    public function test_sending_text_requires_message_body()
    {
        $conversation = Conversation::create([
            'company_id' => $this->company->id,
            'contact_name' => 'John Doe',
            'contact_phone' => '+1234567890',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.v1.chats.messages.text', ['conversation' => $conversation->id]), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_user_can_filter_chats_by_active_and_inactive_tab()
    {
        // Active 24h conversation
        Conversation::create([
            'company_id' => $this->company->id,
            'contact_name' => 'Active Customer',
            'contact_phone' => '+1111111111',
            'status' => 'open',
            'last_message_at' => now(),
            'last_customer_message_at' => now()->subHours(2),
        ]);

        // Inactive conversation
        Conversation::create([
            'company_id' => $this->company->id,
            'contact_name' => 'Inactive Customer',
            'contact_phone' => '+2222222222',
            'status' => 'open',
            'last_message_at' => now()->subDays(5),
            'last_customer_message_at' => now()->subDays(5),
        ]);

        // Test active tab
        $activeResponse = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.chats.index', ['tab' => 'active']));

        $activeResponse->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.contact_name', 'Active Customer');

        // Test inactive tab
        $inactiveResponse = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.v1.chats.index', ['tab' => 'inactive']));

        $inactiveResponse->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.contact_name', 'Inactive Customer');
    }
}
