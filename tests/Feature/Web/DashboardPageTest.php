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
            ->assertRedirect(route('dashboard'));
    }
}
