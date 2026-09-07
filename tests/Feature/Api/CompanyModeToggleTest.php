<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompanyModeToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_without_live_access_cannot_switch_to_active_mode()
    {
        $company = Company::factory()->create([
            'status' => 'demo',
            'can_access_live' => false,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/company/mode-toggle', [
            'status' => 'active',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Live mode access is disabled for your company by system administrator.',
            ]);

        $this->assertEquals('demo', $company->fresh()->status);
    }

    public function test_company_with_live_access_can_toggle_between_demo_and_active()
    {
        $company = Company::factory()->create([
            'status' => 'demo',
            'can_access_live' => true,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        // Switch to Live (active) mode
        $response = $this->postJson('/api/v1/company/mode-toggle', [
            'status' => 'active',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Company mode updated successfully.',
                'data' => [
                    'company_id' => $company->id,
                    'status' => 'active',
                    'is_demo' => false,
                    'can_access_live' => true,
                ],
            ]);

        $this->assertEquals('active', $company->fresh()->status);

        // Switch back to Demo mode
        $responseDemo = $this->postJson('/api/v1/company/mode-toggle', [
            'status' => 'demo',
        ]);

        $responseDemo->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'demo',
                    'is_demo' => true,
                    'can_access_live' => true,
                ],
            ]);

        $this->assertEquals('demo', $company->fresh()->status);
    }

    public function test_mode_toggle_requires_valid_status()
    {
        $company = Company::factory()->create([
            'status' => 'demo',
            'can_access_live' => true,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/company/mode-toggle', [
            'status' => 'invalid_mode',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_auth_me_returns_can_access_live_attribute()
    {
        $company = Company::factory()->create([
            'status' => 'demo',
            'can_access_live' => true,
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'company' => [
                        'can_access_live' => true,
                    ]
                ]
            ]);
    }
}
