<?php

namespace Tests\Feature\SuperAdmin;

use App\Livewire\SuperAdmin\VerificationQueue;
use App\Livewire\SuperAdmin\VerificationReviewWorkspace;
use App\Models\Company;
use App\Models\User;
use App\Models\VerificationTemplate;
use App\Models\DocumentType;
use App\Models\CompanyVerification;
use App\Models\CompanyVerificationDocument;
use App\Models\CompanyVerificationDocumentVersion;
use App\Services\Verification\VerificationWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class VerificationReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Company $company;
    protected VerificationTemplate $template;
    protected DocumentType $docType;
    protected CompanyVerification $verification;
    protected CompanyVerificationDocumentVersion $version;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@platform.local',
        ]);

        $this->company = Company::create([
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
            'primary_email' => 'owner@acme.com',
            'status' => 'active',
            'country' => 'IN',
        ]);

        $this->template = VerificationTemplate::create([
            'name' => 'India Business Verification',
            'country_code' => 'IN',
            'is_active' => true,
        ]);

        $this->docType = DocumentType::create([
            'verification_template_id' => $this->template->id,
            'name' => 'PAN Card Copy',
            'accepted_formats' => 'pdf,jpg',
            'max_size_mb' => 2,
            'is_required' => true,
        ]);

        // Create submission
        $service = app(VerificationWorkflowService::class);
        $this->verification = $service->getOrCreateVerification($this->company);

        $file = UploadedFile::fake()->create('pan_card.pdf', 500, 'application/pdf');
        $this->version = $service->uploadDocument(
            $this->verification,
            $this->docType,
            $file,
            $this->superAdmin
        );
    }

    /** @test */
    public function super_admin_can_access_verification_queue()
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.verification-queue'))
            ->assertOk()
            ->assertSee('Verification Management')
            ->assertSee('Acme Corp');
    }

    /** @test */
    public function super_admin_can_approve_document_which_updates_verification_status()
    {
        $this->assertEquals('under_review', $this->verification->fresh()->status);

        Livewire::actingAs($this->superAdmin)
            ->test(VerificationReviewWorkspace::class, ['id' => $this->verification->id])
            ->set('reviewerNotes', 'Everything looks perfect.')
            ->call('approve', $this->version->id)
            ->assertHasNoErrors();

        $this->assertEquals('approved', $this->version->fresh()->status);
        $this->assertEquals('approved', $this->version->document->fresh()->status);
        
        // Since all required documents are approved, overall status becomes verified
        $this->assertEquals('verified', $this->verification->fresh()->status);
        $this->assertEquals(100, $this->verification->fresh()->progress_percentage);
    }

    /** @test */
    public function super_admin_can_reject_document_requiring_resubmission()
    {
        Livewire::actingAs($this->superAdmin)
            ->test(VerificationReviewWorkspace::class, ['id' => $this->verification->id])
            ->call('openRejectionDialog', $this->version->id)
            ->set('rejectionReason', 'name_mismatch')
            ->set('reviewerNotes', 'Name on PAN card does not match register info.')
            ->call('reject')
            ->assertHasNoErrors();

        $this->assertEquals('rejected', $this->version->fresh()->status);
        $this->assertEquals('rejected', $this->version->document->fresh()->status);
        $this->assertEquals('rejected', $this->verification->fresh()->status);
        $this->assertEquals(0, $this->verification->fresh()->progress_percentage);
    }

    /** @test */
    public function super_admin_can_suspend_and_unsuspend_verification()
    {
        Livewire::actingAs($this->superAdmin)
            ->test(VerificationReviewWorkspace::class, ['id' => $this->verification->id])
            ->call('suspendVerification')
            ->assertSet('verificationId', $this->verification->id);

        $this->assertEquals('suspended', $this->verification->fresh()->status);

        Livewire::actingAs($this->superAdmin)
            ->test(VerificationReviewWorkspace::class, ['id' => $this->verification->id])
            ->call('unsuspendVerification')
            ->assertSet('verificationId', $this->verification->id);

        $this->assertEquals('under_review', $this->verification->fresh()->status);
    }

    /** @test */
    public function super_admin_can_download_all_uploaded_documents()
    {
        $this->actingAs($this->superAdmin);

        // Put a fake file in the storage
        Storage::disk('local')->put($this->version->file_path, 'fake content');

        $response = $this->get(route('superadmin.verification-review.download-all', ['id' => $this->verification->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/zip');
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'company_Acme_Corp_documents.zip'));
    }
}
