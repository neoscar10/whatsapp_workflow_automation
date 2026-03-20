<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Web\Company\CompanyProfilePage;

class CompanyProfilePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function guest_cannot_access_company_profile()
    {
        $this->get(route('company.profile'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_access_company_profile()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@company.com',
            'status' => 'trial',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $this->actingAs($user)
            ->get(route('company.profile'))
            ->assertStatus(200);
    }

    /** @test */
    public function company_profile_fields_load_correctly()
    {
        $company = Company::create([
            'name' => 'Original Name',
            'slug' => 'original-name',
            'primary_email' => 'original@company.com',
            'website_url' => 'https://original.com',
            'description' => 'Original Description',
            'status' => 'trial',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Livewire::actingAs($user)
            ->test(CompanyProfilePage::class)
            ->assertSet('company_name', 'Original Name')
            ->assertSet('contact_email', 'original@company.com')
            ->assertSet('website_url', 'https://original.com')
            ->assertSet('description', 'Original Description');
    }

    /** @test */
    public function valid_update_persists_correctly()
    {
        $company = Company::create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'primary_email' => 'old@company.com',
            'status' => 'trial',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Livewire::actingAs($user)
            ->test(CompanyProfilePage::class)
            ->set('company_name', 'New Name')
            ->set('contact_email', 'new@company.com')
            ->set('website_url', 'https://new.com')
            ->set('description', 'New Description')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Company profile updated successfully.');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'name' => 'New Name',
            'primary_email' => 'new@company.com',
            'website_url' => 'https://new.com',
            'description' => 'New Description',
        ]);
    }

    /** @test */
    public function company_logo_can_be_uploaded()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@company.com',
            'status' => 'trial',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $file = UploadedFile::fake()->image('logo.png');

        Livewire::actingAs($user)
            ->test(CompanyProfilePage::class)
            ->set('logo', $file)
            ->call('save')
            ->assertHasNoErrors();

        $company->refresh();
        $this->assertNotNull($company->logo_path);
        Storage::disk('public')->assertExists($company->logo_path);
    }

    /** @test */
    public function company_logo_can_be_removed()
    {
        Storage::disk('public')->put('logos/test.png', 'fake content');

        $company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@company.com',
            'logo_path' => 'logos/test.png',
            'status' => 'trial',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Livewire::actingAs($user)
            ->test(CompanyProfilePage::class)
            ->call('removeLogo')
            ->assertHasNoErrors();

        $company->refresh();
        $this->assertNull($company->logo_path);
        Storage::disk('public')->assertMissing('logos/test.png');
    }

    /** @test */
    public function contact_email_uniqueness_validation_works()
    {
        Company::create([
            'name' => 'Other Company',
            'slug' => 'other-company',
            'primary_email' => 'other@company.com',
            'status' => 'trial',
        ]);

        $myCompany = Company::create([
            'name' => 'My Company',
            'slug' => 'my-company',
            'primary_email' => 'my@company.com',
            'status' => 'trial',
        ]);

        $user = User::factory()->create([
            'company_id' => $myCompany->id,
        ]);

        Livewire::actingAs($user)
            ->test(CompanyProfilePage::class)
            ->set('contact_email', 'other@company.com')
            ->call('save')
            ->assertHasErrors(['contact_email' => 'unique']);
    }

    /** @test */
    public function discard_changes_restores_persisted_data()
    {
        $company = Company::create([
            'name' => 'Correct Name',
            'slug' => 'correct-name',
            'primary_email' => 'correct@company.com',
            'status' => 'trial',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        Livewire::actingAs($user)
            ->test(CompanyProfilePage::class)
            ->set('company_name', 'Wrong Name')
            ->call('discardChanges')
            ->assertSet('company_name', 'Correct Name');
    }
}
