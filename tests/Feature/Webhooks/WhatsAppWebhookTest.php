<?php

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_verification_succeeds_with_valid_token()
    {
        config(['services.whatsapp.webhook_verify_token' => 'my-secret-token']);

        $response = $this->get('/webhooks/whatsapp/meta?hub_mode=subscribe&hub_challenge=1158201444&hub_verify_token=my-secret-token');

        $response->assertStatus(200);
        $this->assertEquals('1158201444', $response->getContent());
    }

    public function test_webhook_verification_fails_with_invalid_token()
    {
        config(['services.whatsapp.webhook_verify_token' => 'my-secret-token']);

        $response = $this->get('/webhooks/whatsapp/meta?hub_mode=subscribe&hub_challenge=1158201444&hub_verify_token=wrong-token');

        $response->assertStatus(403);
    }

    public function test_webhook_receives_payload_and_returns_200()
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => '1234567890',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => ['display_phone_number' => '123', 'phone_number_id' => '456'],
                                'messages' => [['id' => 'msg123']],
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/webhooks/whatsapp/meta', $payload);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('whatsapp_webhook_events', [
            'event_type' => 'whatsapp_business_account',
            'processing_status' => 'pending',
        ]);
    }
}
