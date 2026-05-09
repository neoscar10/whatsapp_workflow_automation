<?php

namespace Tests\Feature\Api\V1\Contact;

use App\Models\Company;
use App\Models\User;
use App\Models\Contact\Contact;
use App\Models\Contact\ContactGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AudienceGroupMembersApiTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $user;
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'status' => 'active',
            'primary_email' => 'test@example.com',
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'company_id' => $this->company->id,
        ]);

        $this->group = ContactGroup::create([
            'company_id' => $this->company->id,
            'name' => 'Test Group',
            'slug' => 'test-group',
            'type' => 'static',
            'created_by_user_id' => $this->user->id,
        ]);
    }

    protected function getAuthHeader()
    {
        $token = $this->user->createToken('test')->plainTextToken;
        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_unauthenticated_user_cannot_view_available_contacts()
    {
        $response = $this->getJson(route('api.v1.contact-groups.available-contacts', ['group' => $this->group->id]));
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_list_available_contacts_for_own_company_group()
    {
        Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Contact 1',
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->getJson(route('api.v1.contact-groups.available-contacts', ['group' => $this->group->id]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Contact 1');
    }

    public function test_authenticated_user_cannot_access_another_company_group()
    {
        $otherCompany = Company::create([
            'name' => 'Other Company',
            'slug' => 'other-company',
            'status' => 'active',
            'primary_email' => 'other@example.com',
        ]);

        $otherGroup = ContactGroup::create([
            'company_id' => $otherCompany->id,
            'name' => 'Other Group',
            'slug' => 'other-group',
            'type' => 'static',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->getJson(route('api.v1.contact-groups.available-contacts', ['group' => $otherGroup->id]));

        $response->assertStatus(404);
    }

    public function test_authenticated_user_can_add_contacts_from_own_company_to_own_group()
    {
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Contact 1',
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->postJson(route('api.v1.contact-groups.members.store', ['group' => $this->group->id]), [
                'contact_ids' => [$contact->id]
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.added_count', 1)
            ->assertJsonPath('data.member_count', 1);

        $this->assertDatabaseHas('contact_contact_group', [
            'contact_id' => $contact->id,
            'contact_group_id' => $this->group->id,
        ]);
    }

    public function test_duplicate_contacts_are_skipped()
    {
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Contact 1',
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
        ]);

        $this->group->contacts()->attach($contact->id);

        $response = $this->withHeaders($this->getAuthHeader())
            ->postJson(route('api.v1.contact-groups.members.store', ['group' => $this->group->id]), [
                'contact_ids' => [$contact->id]
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'All selected contacts are already members of this audience group.');
    }

    public function test_contacts_from_another_company_cannot_be_added()
    {
        $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other', 'status' => 'active', 'primary_email' => 'o@e.com']);
        $otherContact = Contact::create([
            'company_id' => $otherCompany->id,
            'name' => 'Other Contact',
            'phone' => '0987654321',
            'normalized_phone' => '0987654321',
        ]);

        $response = $this->withHeaders($this->getAuthHeader())
            ->postJson(route('api.v1.contact-groups.members.store', ['group' => $this->group->id]), [
                'contact_ids' => [$otherContact->id]
            ]);

        // It should be skipped by the service layer validation
        $response->assertStatus(200)
            ->assertJsonPath('data.added_count', 0)
            ->assertJsonPath('data.invalid_count', 1);
    }

    public function test_authenticated_user_can_list_group_members()
    {
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Member 1',
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
        ]);

        $this->group->contacts()->attach($contact->id);

        $response = $this->withHeaders($this->getAuthHeader())
            ->getJson(route('api.v1.contact-groups.members', ['group' => $this->group->id]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Member 1');
    }

    public function test_authenticated_user_can_remove_members()
    {
        $contact = Contact::create([
            'company_id' => $this->company->id,
            'name' => 'Member 1',
            'phone' => '1234567890',
            'normalized_phone' => '1234567890',
        ]);

        $this->group->contacts()->attach($contact->id);

        $response = $this->withHeaders($this->getAuthHeader())
            ->deleteJson(route('api.v1.contact-groups.members', ['group' => $this->group->id]), [
                'contact_ids' => [$contact->id]
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.removed_count', 1);

        $this->assertDatabaseMissing('contact_contact_group', [
            'contact_id' => $contact->id,
            'contact_group_id' => $this->group->id,
        ]);
    }
}
