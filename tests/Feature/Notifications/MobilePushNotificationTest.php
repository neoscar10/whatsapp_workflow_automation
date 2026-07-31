<?php

namespace Tests\Feature\Notifications;

use App\Jobs\Notifications\SendMobilePushNotificationJob;
use App\Models\Company;
use App\Models\User;
use App\Models\UserDeviceToken;
use App\Services\Notifications\MobilePushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MobilePushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create(['status' => 'active']);
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
    }

    public function test_job_dispatches_fcm_v1_push_notifications_with_service_account(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'mock_oauth2_token_xyz_987',
                'expires_in' => 3600,
            ], 200),
            'https://fcm.googleapis.com/v1/projects/wa-cloud-platform/messages:send' => Http::response([
                'name' => 'projects/wa-cloud-platform/messages/123456',
            ], 200),
        ]);

        UserDeviceToken::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'device_token' => 'fcm_v1_target_device_token_456',
            'device_type' => 'android',
        ]);

        $job = new SendMobilePushNotificationJob(
            companyId: $this->company->id,
            conversationId: 42,
            messageId: 108,
            senderName: 'John Doe',
            previewText: 'Hello from FCM v1'
        );

        $job->handle(app(MobilePushNotificationService::class));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'fcm.googleapis.com/v1/projects/wa-cloud-platform/messages:send') &&
                   $request['message']['token'] === 'fcm_v1_target_device_token_456' &&
                   $request['message']['data']['conversation_id'] === '42';
        });
    }

    public function test_job_fallback_legacy_fcm_if_no_service_account(): void
    {
        Http::fake([
            'https://fcm.googleapis.com/fcm/send' => Http::response(['success' => 1], 200),
        ]);

        // Point credentials to a non-existent file to force fallback
        config(['services.firebase.credentials' => 'storage/app/firebase/non_existent.json']);
        config(['services.fcm.server_key' => 'mock_fcm_server_key_12345']);

        UserDeviceToken::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'device_token' => 'fcm_legacy_target_device_token_123',
            'device_type' => 'android',
        ]);

        $job = new SendMobilePushNotificationJob(
            companyId: $this->company->id,
            conversationId: 42,
            messageId: 108,
            senderName: 'John Doe',
            previewText: 'Hello from Legacy FCM'
        );

        $job->handle(app(MobilePushNotificationService::class));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://fcm.googleapis.com/fcm/send' &&
                   $request['to'] === 'fcm_legacy_target_device_token_123';
        });
    }
}
