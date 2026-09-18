<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Models\Contact\Contact;
use App\Services\Contact\ContactService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_last_added_contact_is_shown_at_the_top(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);

        $firstContact = Contact::create([
            'company_id' => $company->id,
            'name' => 'First Contact',
            'phone' => '+12345678901',
            'normalized_phone' => '12345678901',
            'created_at' => now()->subHours(2),
        ]);

        $secondContact = Contact::create([
            'company_id' => $company->id,
            'name' => 'Second Contact (Last Added)',
            'phone' => '+12345678902',
            'normalized_phone' => '12345678902',
            'created_at' => now(),
        ]);

        $service = app(ContactService::class);
        $contacts = $service->listForCompany($company->id);

        $this->assertEquals($secondContact->id, $contacts->first()->id);
        $this->assertEquals('Second Contact (Last Added)', $contacts->first()->name);
    }
}
