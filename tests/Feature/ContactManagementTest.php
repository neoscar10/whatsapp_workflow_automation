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
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = \App\Models\Company::create([
            'name' => 'Contact Test Co',
            'slug' => 'contact-test-co',
            'primary_email' => 'contact@test.com',
            'status' => 'demo',
            'demo_credits' => 100.0000,
        ]);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
    }

    public function test_can_list_contacts_api()
    {
        for ($i = 1; $i <= 3; $i++) {
            Contact::create([
                'company_id' => $this->company->id,
                'name' => "Contact {$i}",
                'phone' => "+234800000000{$i}",
                'normalized_phone' => "+234800000000{$i}",
                'status' => 'active',
            ]);
        }

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
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Old Name',
            'phone' => '+2348111112222',
            'normalized_phone' => '+2348111112222',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson("/api/v1/contacts/{$contact->id}", [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $contact->refresh()->name);
    }

    public function test_cannot_access_other_company_contact()
    {
        $otherCompany = \App\Models\Company::create([
            'name' => 'Other Co',
            'slug' => 'other-co',
            'primary_email' => 'other@co.com',
        ]);
        $otherUser = User::factory()->create(['company_id' => $otherCompany->id]);

        $contact = Contact::create([
            'company_id' => $otherCompany->id,
            'name' => 'Other Contact',
            'phone' => '+2348999999999',
            'normalized_phone' => '+2348999999999',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(404);
    }
}
