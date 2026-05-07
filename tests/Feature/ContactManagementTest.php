<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Contact\Contact;
use App\Models\Contact\ContactTag;
use App\Models\Contact\ContactGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_contacts_api()
    {
        Contact::factory()->count(3)->create(['company_id' => $this->user->company_id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/contacts');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_can_create_contact_api()
    {
        $data = [
            'name' => 'Test Contact',
            'phone' => '1234567890',
            'email' => 'test@example.com',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', [
            'company_id' => $this->user->company_id,
            'normalized_phone' => '1234567890',
        ]);
    }

    public function test_can_update_contact_api()
    {
        $contact = Contact::factory()->create(['company_id' => $this->user->company_id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/contacts/{$contact->id}", [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $contact->refresh()->name);
    }

    public function test_cannot_access_other_company_contact()
    {
        $otherUser = User::factory()->create();
        $contact = Contact::factory()->create(['company_id' => $otherUser->company_id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(404);
    }
}
