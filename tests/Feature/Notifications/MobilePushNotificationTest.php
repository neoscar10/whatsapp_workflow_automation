<?php

namespace Tests\Feature\Notifications;

use App\Jobs\Notifications\SendMobilePushNotificationJob;
use App\Models\Company;
use App\Models\User;
use App\Models\UserDeviceToken;
use App\Services\Notifications\MobilePushNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
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

    public function test_job_dispatches_push_notifications_to_registered_company_devices(): void
    {
        Http::fake([
            'https://fcm.googleapis.com/fcm/send' => Http::response(['success' => 1], 200),
        ]);

        config(['services.fcm.server_key' => 'mock_fcm_server_key_12345']);

        UserDeviceToken::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'device_token' => 'fcm_target_device_token_123',
            'device_type' => 'android',
        ]);

        $job = new SendMobilePushNotificationJob(
            companyId: $this->company->id,
            conversationId: 42,
            messageId: 108,
            senderName: 'John Doe',
            previewText: 'Hello from WhatsApp'
        );

        $job->handle(app(MobilePushNotificationService::class));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://fcm.googleapis.com/fcm/send' &&
                   $request['to'] === 'fcm_target_device_token_123' &&
                   $request['data']['conversation_id'] === '42';
        });
    }
}
