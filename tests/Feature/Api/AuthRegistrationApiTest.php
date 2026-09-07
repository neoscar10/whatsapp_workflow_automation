<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_new_company_account_in_demo_mode_via_api()
    {
        $payload = [
            'company_name' => 'Demo Startup Ltd',
            'name' => 'John Founder',
            'email' => 'john@demostartup.com',
            'password' => 'password123',
            'country' => 'IN',
            'device_name' => 'flutter_mobile_app',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'company_id',
                        'is_company_owner',
                        'company' => [
                            'id',
                            'name',
                            'status',
                            'demo_credits',
                            'demo_ends_at',
                            'country',
                        ]
                    ]
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertEquals('Bearer', $response->json('data.token_type'));

        $this->assertDatabaseHas('users', [
            'email' => 'john@demostartup.com',
            'name' => 'John Founder',
            'is_company_owner' => 1,
        ]);

        $user = User::where('email', 'john@demostartup.com')->first();
        $this->assertNotNull($user);

        $company = $user->company;
        $this->assertNotNull($company);
        $this->assertEquals('demo', $company->status);
        $this->assertEquals(100.0000, (float) $company->demo_credits);
        $this->assertNotNull($company->demo_ends_at);

        // Verify wallet created
        $wallet = Wallet::where('user_id', $user->id)->first();
        $this->assertNotNull($wallet);
    }

    public function test_registration_fails_with_duplicate_email()
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@company.com',
        ]);

        $payload = [
            'company_name' => 'Another Company',
            'email' => 'existing@company.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_short_password()
    {
        $payload = [
            'company_name' => 'Valid Company',
            'email' => 'valid@company.com',
            'password' => '123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
