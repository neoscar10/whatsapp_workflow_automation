<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\User;
use App\Models\UserDeviceToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create(['status' => 'active']);
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
    }

    public function test_mobile_app_can_register_fcm_device_token(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson(route('api.v1.devices.token.store'), [
                'device_token' => 'fcm_sample_token_abc_123_xyz',
                'device_type' => 'android',
                'device_name' => 'Pixel 7 Pro',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Device token registered successfully.',
        ]);

        $this->assertDatabaseHas('user_device_tokens', [
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'device_token' => 'fcm_sample_token_abc_123_xyz',
            'device_type' => 'android',
            'device_name' => 'Pixel 7 Pro',
        ]);
    }

    public function test_mobile_app_can_unregister_device_token(): void
    {
        UserDeviceToken::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'device_token' => 'fcm_token_to_delete',
            'device_type' => 'ios',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson(route('api.v1.devices.token.destroy'), [
                'device_token' => 'fcm_token_to_delete',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Device token unregistered successfully.',
        ]);

        $this->assertDatabaseMissing('user_device_tokens', [
            'user_id' => $this->user->id,
            'device_token' => 'fcm_token_to_delete',
        ]);
    }
}
