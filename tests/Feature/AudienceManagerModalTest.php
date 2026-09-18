<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\Contact\Contact;
use App\Models\Contact\ContactGroup;
use App\Livewire\Contacts\AudienceManagerPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AudienceManagerModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_delete_group_modal_triggers_and_deletes(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $group = ContactGroup::create([
            'company_id' => $company->id,
            'name' => 'Audience To Delete',
            'slug' => 'audience-to-delete',
            'type' => 'static',
        ]);

        Livewire::actingAs($user)
            ->test(AudienceManagerPage::class)
            ->call('openDeleteGroupModal', $group->id)
            ->assertSet('showDeleteGroupModal', true)
            ->assertSet('deletingGroupId', $group->id)
            ->assertSet('deletingGroupName', 'Audience To Delete')
            ->call('confirmDeleteGroup')
            ->assertSet('showDeleteGroupModal', false);

        $this->assertSoftDeleted('contact_groups', [
            'id' => $group->id,
        ]);
    }

    public function test_custom_remove_member_modal_triggers_and_removes_member(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $group = ContactGroup::create([
            'company_id' => $company->id,
            'name' => 'Group With Member',
            'slug' => 'group-with-member',
            'type' => 'static',
        ]);

        $contact = Contact::create([
            'company_id' => $company->id,
            'name' => 'Member Contact',
            'phone' => '+18887776666',
            'normalized_phone' => '18887776666',
        ]);

        $group->contacts()->attach($contact->id);

        Livewire::actingAs($user)
            ->test(AudienceManagerPage::class)
            ->call('openMembershipModal', $group->id)
            ->call('openRemoveMemberModal', $contact->id)
            ->assertSet('showRemoveMemberModal', true)
            ->assertSet('removingContactId', $contact->id)
            ->call('confirmRemoveMember')
            ->assertSet('showRemoveMemberModal', false);

        $this->assertDatabaseMissing('contact_contact_group', [
            'contact_group_id' => $group->id,
            'contact_id' => $contact->id,
        ]);
    }
}
