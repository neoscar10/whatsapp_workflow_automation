<?php

namespace Modules\CA\Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CA\Services\CAClientService;
use Modules\CA\Services\RequirementSnapshotService;
use Modules\CA\Services\DeadlineService;
use Modules\CA\Services\DocumentService;
use App\Services\Contact\ContactService;
use App\Models\User;
use App\Models\Company;
use Modules\CA\Models\CABusinessType;
use Modules\CA\Models\CACompliance;
use Modules\CA\Models\CAComplianceRequirement;
use Modules\CA\Models\CAClientComplianceRequirement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CAComplianceEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Modules\CA\Database\Seeders\CADatabaseSeeder']);
    }

    public function test_requirement_snapshot_generation()
    {
        $company = Company::create(['name' => 'Test Firm', 'slug' => 'test-firm', 'primary_email' => 'test@firm.com']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $businessType = CABusinessType::first();

        // Ensure there is at least one master requirement
        $compliance = CACompliance::first();
        CAComplianceRequirement::create([
            'ca_compliance_id' => $compliance->id,
            'name' => 'Test Master Requirement',
            'slug' => 'test-master-req',
            'requirement_type' => 'document',
            'input_type' => 'file',
        ]);

        $service = new CAClientService(new ContactService());
        
        $client = $service->createClient($user, [
            'client_name' => 'Acme Corp',
            'phone' => '+919876543210',
        ], $businessType->id);

        $service->assignCompliances($user, $client, [$compliance->id]);

        $this->assertDatabaseHas('ca_client_compliance_requirements', [
            'name' => 'Test Master Requirement',
            'status' => 'pending',
        ]);
    }

    public function test_secure_document_storage()
    {
        Storage::fake('local');

        $company = Company::create(['name' => 'Test Firm', 'slug' => 'test-firm', 'primary_email' => 'test@firm.com']);
        $user = User::factory()->create(['company_id' => $company->id]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $documentService = new DocumentService();
        $document = $documentService->storeDocument($file, $user, [
            'document_name' => 'Test Document',
        ]);

        $this->assertDatabaseHas('ca_documents', [
            'company_id' => $company->id,
            'document_name' => 'Test Document',
        ]);

        Storage::disk('local')->assertExists($document->storage_path);
        
        // Assert secure path retrieval works for the correct user
        $securePath = $documentService->getSecurePath($document, $user);
        $this->assertStringContainsString('ca_documents', $securePath);
    }
}
