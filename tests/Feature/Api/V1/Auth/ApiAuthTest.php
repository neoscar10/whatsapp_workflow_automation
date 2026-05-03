<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function createUser(array $overrides = [])
    {
        $company = Company::create([
            'name' => 'Test Company',
            'slug' => 'test-company',
            'status' => 'active',
            'primary_email' => 'test@example.com',
        ]);

        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'company_id' => $company->id,
        ], $overrides));
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = $this->createUser();

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'user@example.com',
            'password' => 'password123',
            'device_name' => 'test_device',
        ]);

        $response->assertStatus(200)
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
                    ],
                ],
            ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test_device',
        ]);
    }

    public function test_user_cannot_login_with_invalid_password()
    {
        $this->createUser();

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => 'user@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_authenticated_user_can_access_me_endpoint()
    {
        $user = $this->createUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(route('api.v1.auth.me'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_me_endpoint()
    {
        $response = $this->getJson(route('api.v1.auth.me'));

        $response->assertStatus(401);
    }

    public function test_user_can_logout()
    {
        $user = $this->createUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.v1.auth.logout'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        $this->assertCount(0, $user->tokens);
    }

    public function test_user_can_logout_from_all_devices()
    {
        $user = $this->createUser();
        $user->createToken('device1');
        $user->createToken('device2');
        $token = $user->createToken('device3')->plainTextToken;

        $this->assertCount(3, $user->tokens);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson(route('api.v1.auth.logout-all'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'All sessions logged out successfully.',
            ]);

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
