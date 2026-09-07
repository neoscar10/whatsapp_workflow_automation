<?php

namespace Tests\Feature\Api\V1\WhatsApp;

use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppSetupApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_user_gets_virtual_connected_account()
    {
        $demoCompany = Company::create([
            'name' => 'Demo Enterprise',
            'slug' => 'demo-enterprise',
            'status' => 'demo',
            'primary_email' => 'demo@enterprise.com',
        ]);

        $demoUser = User::create([
            'name' => 'Demo Owner',
            'email' => 'owner@demoenterprise.com',
            'password' => bcrypt('password'),
            'company_id' => $demoCompany->id,
        ]);

        $response = $this->actingAs($demoUser, 'sanctum')
            ->getJson(route('api.v1.whatsapp.setup.account'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.connection_status', 'connected')
            ->assertJsonPath('data.waba_id', 'demo_waba_sandbox_id');
    }

    public function test_demo_user_gets_fallback_demo_phone_number()
    {
        $systemDemo = Company::create([
            'name' => 'System Demo',
            'slug' => 'system-demo',
            'status' => 'active',
            'primary_email' => 'demo-system@platform.local',
        ]);

        $systemNumber = WhatsAppPhoneNumber::create([
            'company_id' => $systemDemo->id,
            'display_name' => 'Sandbox Demo WhatsApp',
            'phone_number' => '+1555019999',
            'phone_number_id' => '100200300',
            'status' => 'active',
        ]);

        $demoCompany = Company::create([
            'name' => 'New Demo Company',
            'slug' => 'new-demo-company',
            'status' => 'demo',
            'primary_email' => 'new@demo.com',
        ]);

        $demoUser = User::create([
            'name' => 'New Demo User',
            'email' => 'user@newdemo.com',
            'password' => bcrypt('password'),
            'company_id' => $demoCompany->id,
        ]);

        $response = $this->actingAs($demoUser, 'sanctum')
            ->getJson(route('api.v1.whatsapp.setup.phone-numbers.index'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.phone_number', '+1555019999');
    }
}
