<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard()
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard Overview');
        $response->assertSee('Recent Activity');
        $response->assertSee('Active Campaigns');
    }

    public function test_panel_route_redirects_to_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('panel.home'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_login_redirects_to_dashboard_after_success()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        \Livewire\Livewire::test(\App\Livewire\Web\Auth\LoginPage::class)
            ->set('email', $user->email)
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('chats.index'));
    }

    public function test_dashboard_displays_demo_mode_badge_for_demo_companies()
    {
        $company = \App\Models\Company::create([
            'name' => 'Demo Web Co',
            'slug' => 'demo-web-co',
            'status' => 'demo',
            'primary_email' => 'demoweb@co.com',
        ]);

        $user = User::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Demo Mode');
    }

    public function test_dashboard_displays_verification_alert_when_pending()
    {
        $company = \App\Models\Company::create([
            'name' => 'Pending Verification Co',
            'slug' => 'pending-verif-co',
            'status' => 'active',
            'primary_email' => 'pending@co.com',
        ]);

        $user = User::factory()->create(['company_id' => $company->id]);

        \App\Models\CompanyVerification::create([
            'company_id' => $company->id,
            'status' => 'under_review',
            'progress_percentage' => 50,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Business Verification Pending');
        $response->assertSee('Under Review');
    }
}
