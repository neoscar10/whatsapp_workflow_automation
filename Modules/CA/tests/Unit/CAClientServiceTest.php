<?php

namespace Modules\CA\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CA\Services\CAClientService;
use App\Services\Contact\ContactService;
use App\Models\User;
use App\Models\Company;
use Modules\CA\Models\CABusinessType;
use Modules\CA\Models\CACompliance;
use Illuminate\Support\Facades\Event;
use Modules\CA\Events\ClientCreated;
use Modules\CA\Events\CompliancesAssigned;

class CAClientServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Modules\CA\Database\Seeders\CADatabaseSeeder']);
    }

    public function test_client_creation_and_contact_sync()
    {
        Event::fake([ClientCreated::class]);

        $company = Company::create(['name' => 'Test Firm', 'slug' => 'test-firm', 'primary_email' => 'test@firm.com']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $businessType = CABusinessType::first();

        $service = new CAClientService(new ContactService());

        $client = $service->createClient($user, [
            'client_name' => 'Acme Corp',
            'phone' => '+919876543210',
            'email' => 'contact@acmecorp.com',
            'address' => '123 Test St',
        ], $businessType->id);

        $this->assertDatabaseHas('ca_clients', [
            'client_name' => 'Acme Corp',
            'company_id' => $company->id,
            'ca_business_type_id' => $businessType->id,
        ]);

        $this->assertDatabaseHas('contacts', [
            'name' => 'Acme Corp',
            'phone' => '+919876543210',
            'company_id' => $company->id,
        ]);

        $this->assertNotNull($client->contact_id);

        Event::assertDispatched(ClientCreated::class);
    }

    public function test_compliance_assignment()
    {
        Event::fake([CompliancesAssigned::class]);

        $company = Company::create(['name' => 'Test Firm', 'slug' => 'test-firm', 'primary_email' => 'test@firm.com']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $businessType = CABusinessType::first();

        $service = new CAClientService(new ContactService());

        $client = $service->createClient($user, [
            'client_name' => 'Acme Corp',
            'phone' => '+919876543211',
        ], $businessType->id);

        $complianceIds = CACompliance::take(2)->pluck('id')->toArray();

        $service->assignCompliances($user, $client, $complianceIds);

        $this->assertDatabaseHas('ca_client_compliances', [
            'ca_client_id' => $client->id,
            'ca_compliance_id' => $complianceIds[0],
            'assigned_by' => $user->id,
        ]);

        Event::assertDispatched(CompliancesAssigned::class);
    }
}
