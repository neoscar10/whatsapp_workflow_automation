<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\Company;
use App\Models\User;
use App\Models\VerificationTemplate;
use App\Models\DocumentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VerificationTemplateConfigTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $companyOwner;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@platform.local',
            'company_id' => null,
        ]);

        $this->company = Company::create([
            'name' => 'Acme Corp',
            'slug' => 'acme-corp',
            'primary_email' => 'owner@acme.com',
            'status' => 'active',
        ]);

        $this->companyOwner = User::factory()->create([
            'role' => 'user',
            'company_id' => $this->company->id,
            'is_company_owner' => true,
        ]);
    }

    /** @test */
    public function guests_are_redirected_to_login()
    {
        $this->get('/super-admin/verification-templates')
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function standard_users_are_redirected_away()
    {
        $this->actingAs($this->companyOwner)
            ->get('/super-admin/verification-templates')
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function super_admin_can_access_verification_templates_page()
    {
        $this->actingAs($this->superAdmin)
            ->get('/super-admin/verification-templates')
            ->assertOk()
            ->assertSee('Verification Checklists');
    }

    /** @test */
    public function seeded_defaults_are_loaded_correctly()
    {
        // Run seeders
        $this->seed(\Database\Seeders\VerificationTemplateSeeder::class);

        $this->assertDatabaseHas('verification_templates', [
            'name' => 'India WhatsApp Business Verification Template',
            'country_code' => 'IN',
        ]);

        $this->assertDatabaseCount('document_types', 10);
        $this->assertDatabaseHas('document_types', [
            'name' => 'Certificate of Incorporation',
            'is_required' => true,
        ]);
        $this->assertDatabaseHas('document_types', [
            'name' => 'Optional Trademark Certificate',
            'is_required' => false,
        ]);
    }

    /** @test */
    public function super_admin_can_crud_verification_templates()
    {
        // 1. Create a Template
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('openCreateTemplateModal')
            ->set('templateName', 'Nigeria WABA Verification')
            ->set('templateCountryCode', 'NG')
            ->set('templateDescription', 'Nigeria verification details')
            ->set('templateIsActive', true)
            ->set('templateSortOrder', 2)
            ->call('saveTemplate')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('verification_templates', [
            'name' => 'Nigeria WABA Verification',
            'country_code' => 'NG',
            'sort_order' => 2,
        ]);

        $template = VerificationTemplate::first();

        // 2. Edit Template
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('openEditTemplateModal', $template->id)
            ->set('templateName', 'Nigeria WABA Requirements')
            ->call('saveTemplate')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('verification_templates', [
            'id' => $template->id,
            'name' => 'Nigeria WABA Requirements',
        ]);

        // 3. Toggle status
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('toggleTemplateStatus', $template->id);

        $this->assertFalse($template->fresh()->is_active);

        // 4. Move up/down order
        $template2 = VerificationTemplate::create([
            'name' => 'UK WABA',
            'country_code' => 'GB',
            'sort_order' => 5,
        ]);

        $template->update(['sort_order' => 6]);

        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('moveUpTemplate', $template->id);

        // Orders swapped
        $this->assertEquals(5, $template->fresh()->sort_order);
        $this->assertEquals(6, $template2->fresh()->sort_order);

        // 5. Delete Template
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('deleteTemplate', $template->id);

        $this->assertDatabaseMissing('verification_templates', [
            'id' => $template->id,
        ]);
    }

    /** @test */
    public function super_admin_can_crud_document_types_for_template()
    {
        $template = VerificationTemplate::create([
            'name' => 'Nigeria WABA Verification',
            'country_code' => 'NG',
            'sort_order' => 1,
        ]);

        // 1. Add Document Type
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('selectedTemplateId', $template->id)
            ->call('openCreateDocumentModal')
            ->set('docName', 'CAC Registration Copy')
            ->set('docDescription', 'Corporate Affairs Commission copy')
            ->set('docPlaceholder', 'Upload CAC copy')
            ->set('docAcceptedFormats', 'pdf,png')
            ->set('docMaxSizeMb', 5)
            ->set('docIsRequired', true)
            ->set('docIsActive', true)
            ->set('docSortOrder', 1)
            ->call('saveDocument')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('document_types', [
            'verification_template_id' => $template->id,
            'name' => 'CAC Registration Copy',
            'max_size_mb' => 5,
            'is_required' => true,
        ]);

        $doc = DocumentType::first();

        // 2. Edit Document Type
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('selectedTemplateId', $template->id)
            ->call('openEditDocumentModal', $doc->id)
            ->set('docName', 'CAC Document Copy')
            ->call('saveDocument')
            ->assertHasNoErrors();

        $this->assertEquals('CAC Document Copy', $doc->fresh()->name);

        // 3. Toggle required and active states
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('selectedTemplateId', $template->id)
            ->call('toggleDocumentRequiredStatus', $doc->id)
            ->call('toggleDocumentStatus', $doc->id);

        $this->assertFalse($doc->fresh()->is_required);
        $this->assertFalse($doc->fresh()->is_active);

        // 4. Move up/down order
        $doc2 = DocumentType::create([
            'verification_template_id' => $template->id,
            'name' => 'Tax Clearance',
            'is_required' => true,
            'sort_order' => 4,
        ]);

        $doc->update(['sort_order' => 5]);

        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('selectedTemplateId', $template->id)
            ->call('moveUpDocument', $doc->id);

        $this->assertEquals(4, $doc->fresh()->sort_order);
        $this->assertEquals(5, $doc2->fresh()->sort_order);

        // 5. Delete Document Type
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('selectedTemplateId', $template->id)
            ->call('deleteDocument', $doc->id);

        $this->assertDatabaseMissing('document_types', [
            'id' => $doc->id,
        ]);
    }

    /** @test */
    public function template_disable_requires_confirmation()
    {
        $template = VerificationTemplate::create([
            'name' => 'Test active template',
            'is_active' => true,
        ]);

        // Requesting disable sets confirmation ID
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('requestDisableTemplate', $template->id)
            ->assertSet('confirmingDisableTemplateId', $template->id);

        // Template is still active
        $this->assertTrue($template->fresh()->is_active);

        // Confirming disable toggles status and clears ID
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('confirmingDisableTemplateId', $template->id)
            ->call('confirmDisableTemplate')
            ->assertSet('confirmingDisableTemplateId', null);

        $this->assertFalse($template->fresh()->is_active);

        // Requesting disable on inactive template toggles directly without confirmation
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('requestDisableTemplate', $template->id)
            ->assertSet('confirmingDisableTemplateId', null);

        $this->assertTrue($template->fresh()->is_active);

        // Canceling disable clears confirmation ID and leaves template unchanged
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('confirmingDisableTemplateId', $template->id)
            ->call('cancelDisableTemplate')
            ->assertSet('confirmingDisableTemplateId', null);

        $this->assertTrue($template->fresh()->is_active);
    }

    /** @test */
    public function document_disable_requires_confirmation()
    {
        $template = VerificationTemplate::create([
            'name' => 'Test template',
            'is_active' => true,
        ]);

        $doc = DocumentType::create([
            'verification_template_id' => $template->id,
            'name' => 'Test document',
            'is_active' => true,
        ]);

        // Requesting disable sets confirmation ID
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('requestDisableDocument', $doc->id)
            ->assertSet('confirmingDisableDocumentId', $doc->id);

        // Document is still active
        $this->assertTrue($doc->fresh()->is_active);

        // Confirming disable toggles status and clears ID
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('confirmingDisableDocumentId', $doc->id)
            ->call('confirmDisableDocument')
            ->assertSet('confirmingDisableDocumentId', null);

        $this->assertFalse($doc->fresh()->is_active);

        // Requesting disable on inactive document toggles directly without confirmation
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('requestDisableDocument', $doc->id)
            ->assertSet('confirmingDisableDocumentId', null);

        $this->assertTrue($doc->fresh()->is_active);

        // Canceling disable clears confirmation ID and leaves document unchanged
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('confirmingDisableDocumentId', $doc->id)
            ->call('cancelDisableDocument')
            ->assertSet('confirmingDisableDocumentId', null);

        $this->assertTrue($doc->fresh()->is_active);
    }

    /** @test */
    public function documents_can_be_reordered_via_drag_and_drop()
    {
        $template = VerificationTemplate::create([
            'name' => 'Test Template',
            'is_active' => true,
        ]);

        $doc1 = DocumentType::create([
            'verification_template_id' => $template->id,
            'name' => 'Doc 1',
            'sort_order' => 1,
        ]);

        $doc2 = DocumentType::create([
            'verification_template_id' => $template->id,
            'name' => 'Doc 2',
            'sort_order' => 2,
        ]);

        $doc3 = DocumentType::create([
            'verification_template_id' => $template->id,
            'name' => 'Doc 3',
            'sort_order' => 3,
        ]);

        // Drag Doc 3 (from) and drop on Doc 1 (to)
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->set('selectedTemplateId', $template->id)
            ->call('reorderDocuments', $doc3->id, $doc1->id);

        // Sort order should be updated: Doc 3 -> Doc 1 -> Doc 2
        $this->assertEquals(1, $doc3->fresh()->sort_order);
        $this->assertEquals(2, $doc1->fresh()->sort_order);
        $this->assertEquals(3, $doc2->fresh()->sort_order);
    }

    /** @test */
    public function template_defaults_to_india_country_code()
    {
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('openCreateTemplateModal')
            ->assertSet('templateCountryCode', 'IN');
    }

    /** @test */
    public function template_invalid_country_code_fails_validation()
    {
        Livewire::actingAs($this->superAdmin)
            ->test(\App\Livewire\SuperAdmin\VerificationTemplateConfig::class)
            ->call('openCreateTemplateModal')
            ->set('templateName', 'Invalid Verification')
            ->set('templateCountryCode', 'XX')
            ->call('saveTemplate')
            ->assertHasErrors(['templateCountryCode' => 'in']);
    }
}
