<?php

namespace Tests\Feature\WhatsApp;

use App\Models\Company;
use App\Models\User;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\WhatsApp\WhatsAppPhoneNumberSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppAccountSyncTest extends TestCase
{
    use RefreshDatabase;

    private function setupUserAndCompany()
    {
        $company = Company::forceCreate([
            'name' => 'Test Company',
            'slug' => 'test-company-' . uniqid(),
            'primary_email' => uniqid() . 'test@example.com'
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        return [$user, $company];
    }

    public function test_sync_successfully_fetches_and_matches_phone_numbers()
    {
        [$user, $company] = $this->setupUserAndCompany();
        
        $account = WhatsAppAccount::forceCreate([
            'company_id' => $company->id,
            'waba_id' => '123456789',
            'access_token' => 'valid-token',
            'connection_status' => 'pending-sync'
        ]);

        // Local number already exists but without Meta ID (placeholder)
        $localNumber = WhatsAppPhoneNumber::forceCreate([
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'display_name' => 'Original Name',
            'phone_number_id' => 'MANUAL_123',
            'phone_number' => '15551234567',
            'status' => 'active'
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'data' => [
                    [
                        'id' => '987654321', // This matches new ID
                        'display_phone_number' => '+1 555-123-4567', // Should match by number string
                        'verified_name' => 'Meta Verified Name',
                        'quality_rating' => 'GREEN',
                        'status' => 'APPROVED',
                    ],
                    [
                        'id' => '111222333',
                        'display_phone_number' => '+1 555-000-0000',
                        'verified_name' => 'New Number',
                        'quality_rating' => 'YELLOW',
                        'status' => 'PENDING',
                    ]
                ]
            ], 200)
        ]);

        $service = app(WhatsAppPhoneNumberSyncService::class);
        $result = $service->syncForAccount($account);

        $this->assertTrue($result['success']);
        
        // Check local number was updated
        $localNumber->refresh();
        $this->assertEquals('987654321', $localNumber->phone_number_id);
        $this->assertEquals('Meta Verified Name', $localNumber->verified_name);
        $this->assertEquals('GREEN', $localNumber->quality_rating);
        $this->assertEquals('approved', $localNumber->status);

        file_put_contents('whatsapp_db_debug.json', json_encode(WhatsAppPhoneNumber::all()->toArray(), JSON_PRETTY_PRINT));

        // Check new number was created
        $newNumber = WhatsAppPhoneNumber::where('phone_number_id', '111222333')->first();
        $this->assertNotNull($newNumber, 'Phone number 111222333 was not created');
        $this->assertEquals('New Number', $newNumber->verified_name);
        $this->assertEquals('pending', $newNumber->status);
        $this->assertEquals('YELLOW', $newNumber->quality_rating);

        // Check account status
        $account->refresh();
        $this->assertEquals('connected', $account->connection_status);
        $this->assertNotNull($account->last_synced_at);
        $this->assertNull($account->last_sync_error);
    }

    public function test_sync_failure_updates_account_error_state()
    {
        [$user, $company] = $this->setupUserAndCompany();
        
        $account = WhatsAppAccount::forceCreate([
            'company_id' => $company->id,
            'waba_id' => '123456789',
            'access_token' => 'invalid-token',
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'error' => [
                    'message' => 'Invalid OAuth access token.',
                    'type' => 'OAuthException',
                    'code' => 190
                ]
            ], 401)
        ]);

        $service = app(WhatsAppPhoneNumberSyncService::class);
        $result = $service->syncForAccount($account);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid OAuth access token.', $result['message']);

        $account->refresh();
        $this->assertEquals('Invalid OAuth access token.', $account->last_sync_error);
        $this->assertNotNull($account->last_synced_at);
    }

    public function test_unmatched_local_numbers_marked_inactive_with_error()
    {
        [$user, $company] = $this->setupUserAndCompany();
        
        $account = WhatsAppAccount::forceCreate([
            'company_id' => $company->id,
            'waba_id' => '123456789',
            'access_token' => 'valid-token',
        ]);

        $orphanNumber = WhatsAppPhoneNumber::forceCreate([
            'company_id' => $company->id,
            'whatsapp_account_id' => $account->id,
            'display_name' => 'Orphan Number',
            'phone_number_id' => 'GONE_FROM_META',
            'status' => 'active'
        ]);

        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'data' => [] // Empty list from Meta
            ], 200)
        ]);

        $service = app(WhatsAppPhoneNumberSyncService::class);
        $service->syncForAccount($account);

        $orphanNumber->refresh();
        $this->assertEquals('inactive', $orphanNumber->status);
        $this->assertStringContainsString('not found in Meta response', $orphanNumber->last_sync_error);
    }
}
