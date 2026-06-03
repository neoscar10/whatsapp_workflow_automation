<?php

namespace Tests\Feature\Web;

use App\Livewire\Web\Company\BusinessVerificationDashboard;
use App\Models\Company;
use App\Models\User;
use App\Models\VerificationTemplate;
use App\Models\DocumentType;
use App\Models\CompanyVerification;
use App\Models\CompanyVerificationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;
    protected VerificationTemplate $template;
    protected DocumentType $docType1;
    protected DocumentType $docType2;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->company = Company::create([
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
            'primary_email' => 'owner@acme.com',
            'status' => 'active',
            'country' => 'IN',
        ]);

        $this->user = User::factory()->create([
            'role' => 'user',
            'company_id' => $this->company->id,
            'is_company_owner' => true,
        ]);

        // Preseed a verification checklist for India
        $this->template = VerificationTemplate::create([
            'name' => 'India Business Verification',
            'country_code' => 'IN',
            'is_active' => true,
        ]);

        $this->docType1 = DocumentType::create([
            'verification_template_id' => $this->template->id,
            'name' => 'PAN Card Copy',
            'description' => 'Permanent Account Number card copy',
            'accepted_formats' => 'pdf,jpg,png',
            'max_size_mb' => 2,
            'is_required' => true,
            'is_active' => true,
        ]);

        $this->docType2 = DocumentType::create([
            'verification_template_id' => $this->template->id,
            'name' => 'Optional Document',
            'description' => 'An optional document',
            'accepted_formats' => 'pdf,jpg',
            'max_size_mb' => 5,
            'is_required' => false,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function guests_cannot_access_verification_dashboard()
    {
        $this->get(route('company.verification'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function user_can_access_verification_dashboard_and_checklist_is_synchronized()
    {
        $this->actingAs($this->user)
            ->get(route('company.verification'))
            ->assertOk()
            ->assertSee('Business Verification')
            ->assertSee('PAN Card Copy')
            ->assertSee('Optional Document');

        $verification = CompanyVerification::where('company_id', $this->company->id)->first();
        $this->assertNotNull($verification);
        $this->assertEquals('not_started', $verification->status);
        $this->assertEquals(0, $verification->progress_percentage);
    }

    /** @test */
    public function company_user_can_upload_valid_document_which_creates_a_new_version()
    {
        $file = UploadedFile::fake()->create('pan_card.pdf', 500, 'application/pdf');

        Livewire::actingAs($this->user)
            ->test(BusinessVerificationDashboard::class)
            ->call('openUploadModal', $this->docType1->id)
            ->set('file', $file)
            ->set('issueDate', '2026-01-01')
            ->set('expiryDate', '2030-01-01')
            ->call('submitDocument')
            ->assertHasNoErrors();

        $verification = CompanyVerification::where('company_id', $this->company->id)->first();
        $this->assertEquals('under_review', $verification->status);
        
        $compDoc = CompanyVerificationDocument::where('company_verification_id', $verification->id)
            ->where('document_type_id', $this->docType1->id)
            ->first();
        $this->assertEquals('pending_review', $compDoc->status);
        
        $version = $compDoc->latestVersion;
        $this->assertNotNull($version);
        $this->assertEquals(1, $version->version_number);
        $this->assertEquals('pending_review', $version->status);
        $this->assertEquals('pan_card.pdf', $version->file_name);
        $this->assertEquals('2026-01-01', $version->issue_date->format('Y-m-d'));
        $this->assertEquals('2030-01-01', $version->expiry_date->format('Y-m-d'));

        // Check storage
        Storage::disk('local')->assertExists($version->file_path);
    }

    /** @test */
    public function uploading_invalid_format_is_rejected()
    {
        $file = UploadedFile::fake()->create('pan_card.txt', 100, 'text/plain');

        Livewire::actingAs($this->user)
            ->test(BusinessVerificationDashboard::class)
            ->call('openUploadModal', $this->docType1->id)
            ->set('file', $file)
            ->call('submitDocument')
            ->assertHasErrors(['file']);
    }

    /** @test */
    public function uploading_file_exceeding_max_size_is_rejected()
    {
        $file = UploadedFile::fake()->create('pan_card.pdf', 3000, 'application/pdf'); // 3MB exceeds 2MB limit

        Livewire::actingAs($this->user)
            ->test(BusinessVerificationDashboard::class)
            ->call('openUploadModal', $this->docType1->id)
            ->set('file', $file)
            ->call('submitDocument')
            ->assertHasErrors(['file']);
    }
}
