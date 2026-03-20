<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Web\Auth\LoginPage;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_page_loads_successfully()
    {
        $this->get(route('login'))->assertStatus(200);
    }

    /** @test */
    public function valid_credentials_authenticate_successfully_and_redirect_to_panel()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@company.com',
            'status' => 'trial',
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'email' => 'test@company.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', 'test@company.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('panel.home'));

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function invalid_credentials_are_rejected_with_error()
    {
        $company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'primary_email' => 'test@company.com',
            'status' => 'trial',
        ]);

        User::factory()->create([
            'company_id' => $company->id,
            'email' => 'test@company.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', 'test@company.com')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    /** @test */
    public function authenticated_user_is_redirected_away_from_login_page()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('panel.home'));
    }

    /** @test */
    public function panel_home_page_is_protected_by_auth_middleware()
    {
        $this->get(route('panel.home'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function suspended_company_user_cannot_login()
    {
        $company = Company::create([
            'name' => 'Bad Company',
            'slug' => 'bad-company',
            'primary_email' => 'bad@company.com',
            'status' => 'suspended',
        ]);

        User::factory()->create([
            'company_id' => $company->id,
            'email' => 'bad@company.com',
            'password' => bcrypt('password123'),
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', 'bad@company.com')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }
}
