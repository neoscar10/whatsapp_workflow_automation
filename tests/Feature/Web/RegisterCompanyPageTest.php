<?php

namespace Tests\Feature\Web;

use App\Livewire\Web\Auth\RegisterCompanyPage;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterCompanyPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function home_page_loads_successfully()
    {
        $this->get('/')->assertStatus(200);
    }

    /** @test */
    public function can_create_company_manually()
    {
        Company::create([
            'name' => 'Manual Co',
            'slug' => 'manual-co',
            'primary_email' => 'manual@co.com',
            'status' => 'trial',
        ]);
        
        $this->assertDatabaseHas('companies', ['name' => 'Manual Co']);
    }

    /** @test */
    public function valid_company_registration_creates_company_and_owner_user_and_auto_logins()
    {
        Livewire::test(RegisterCompanyPage::class)
            ->set('company_name', 'Test Company')
            ->set('email', 'test@company.com')
            ->set('password', 'password123')
            ->set('agree_to_terms', true)
            ->call('register')
            ->assertHasNoErrors()
            ->assertSessionHas('success')
            ->assertRedirect(route('dashboard'));
            
        $this->assertAuthenticated();

        $this->assertDatabaseHas('companies', [
            'name' => 'Test Company',
            'primary_email' => 'test@company.com',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@company.com',
            'is_company_owner' => true,
        ]);
    }

    /** @test */
    public function duplicate_email_is_rejected()
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        Livewire::test(RegisterCompanyPage::class)
            ->set('company_name', 'Other Company')
            ->set('email', 'duplicate@example.com')
            ->set('password', 'password123')
            ->set('agree_to_terms', true)
            ->call('register')
            ->assertHasErrors(['email' => 'unique']);
    }

    /** @test */
    public function agree_to_terms_checkbox_is_required()
    {
        Livewire::test(RegisterCompanyPage::class)
            ->set('company_name', 'Test Company')
            ->set('email', 'test@company.com')
            ->set('password', 'password123')
            ->set('agree_to_terms', false)
            ->call('register')
            ->assertHasErrors(['agree_to_terms']);
    }
}
