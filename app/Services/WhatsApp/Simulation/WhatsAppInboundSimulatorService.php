<?php

namespace App\Services\WhatsApp\Simulation;

use App\Models\Contact\Contact;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Services\WhatsApp\WhatsAppWebhookEventService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppInboundSimulatorService
{
    public function __construct(
        protected WhatsAppWebhookEventService $webhookEventService,
        protected SimulatedWhatsAppMediaResolver $mediaResolver
    ) {}

    /**
     * Simulate an inbound WhatsApp message from a contact.
     */
    public function simulate(int $contactId, ?string $body = null, ?UploadedFile $file = null, int $userId): array
    {
        if (!config('services.whatsapp.simulator.enabled') && app()->environment() !== 'local') {
            throw new Exception("WhatsApp Simulator is not enabled in this environment.");
        }

        // 1. Fetch Contact & Enforce Company Isolation
        $contact = Contact::findOrFail($contactId);
        $user = \App\Models\User::findOrFail($userId);

        if ($contact->company_id !== $user->company_id) {
            throw new Exception("Unauthorized access: Contact does not belong to your company.");
        }

        // 2. Ensure Fake/Mock WhatsApp Phone Number Setup exists
        $localNumber = $this->ensureFakePhoneNumber($contact->company_id, $userId);

        $fakePhoneId = $localNumber->phone_number_id;
        $fakeWabaId = $localNumber->account->waba_id;
        
        $contactPhone = $contact->phone;
        $contactName = $contact->name ?: $contactPhone;
        $timestamp = time();
        $messageId = 'wamid.simulated_' . Str_random_hex(16);

        // Determine message type
        $type = 'text';
        $messageContent = [];

        if ($file) {
            $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
            $simMediaId = $this->mediaResolver->storeSimulatedUpload($file, $contact->company_id, $contactId, $userId);

            if (str_starts_with($mimeType, 'image/')) {
                $type = 'image';
                $messageContent = [
                    'image' => [
                        'id' => $simMediaId,
                        'mime_type' => $mimeType,
                        'sha256' => hash('sha256', $simMediaId),
                        'caption' => $body ?: null,
                    ]
                ];
            } else {
                $type = 'document';
                $messageContent = [
                    'document' => [
                        'id' => $simMediaId,
                        'mime_type' => $mimeType,
                        'sha256' => hash('sha256', $simMediaId),
                        'filename' => $file->getClientOriginalName() ?: 'document.pdf',
                        'caption' => $body ?: null,
                    ]
                ];
            }
        } else {
            $type = 'text';
            $messageContent = [
                'text' => [
                    'body' => $body ?? 'Simulated Message',
                ]
            ];
        }

        // 3. Assemble Meta-compliant payload
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => $fakeWabaId,
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'display_phone_number' => $localNumber->phone_number,
                                    'phone_number_id' => $fakePhoneId,
                                ],
                                'contacts' => [
                                    [
                                        'profile' => [
                                            'name' => $contactName,
                                        ],
                                        'wa_id' => $contactPhone,
                                    ]
                                ],
                                'messages' => [
                                    array_merge([
                                        'from' => $contactPhone,
                                        'id' => $messageId,
                                        'timestamp' => (string)$timestamp,
                                        'type' => $type,
                                        // Include simulator metadata context
                                        'simulator_metadata' => [
                                            'simulated' => true,
                                            'simulator_user_id' => $userId,
                                            'simulated_contact_id' => $contactId,
                                            'simulated_at' => now()->toDateTimeString(),
                                        ]
                                    ], $messageContent)
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // 4. Pass directly to webhook events processing service
        $this->webhookEventService->handle($payload);

        return [
            'success' => true,
            'message_id' => $messageId,
            'type' => $type,
        ];
    }

    /**
     * Retrieve or dynamically register a dummy WhatsAppPhoneNumber.
     */
    public function ensureFakePhoneNumber(int $companyId, int $userId): WhatsAppPhoneNumber
    {
        $fakePhoneId = config('services.whatsapp.simulator.fake_phone_number_id', 'LOCAL_PHONE_NUMBER_ID');
        $fakeWabaId = config('services.whatsapp.simulator.fake_waba_id', 'SIMULATED_WABA_ID');

        // Check if there is already a number with the fake phone number ID
        $number = WhatsAppPhoneNumber::with('account')->where('phone_number_id', $fakePhoneId)->first();
        if ($number) {
            return $number;
        }

        // Create a fake account first
        $account = WhatsAppAccount::firstOrCreate(
            ['company_id' => $companyId, 'waba_id' => $fakeWabaId],
            [
                'access_token' => 'fake_access_token',
                'business_id' => 'fake_business_id',
                'connection_status' => 'connected',
                'webhook_status' => 'verified',
                'webhook_subscription_status' => 'subscribed',
            ]
        );

        return WhatsAppPhoneNumber::create([
            'company_id' => $companyId,
            'whatsapp_account_id' => $account->id,
            'display_name' => 'Local Simulator Number',
            'phone_number_id' => $fakePhoneId,
            'phone_number' => '+15550000000',
            'status' => 'approved',
            'verified_name' => 'Local Simulator Number',
            'quality_rating' => 'GREEN',
            'created_by_user_id' => $userId,
        ]);
    }
}

/**
 * Generate a random hex string.
 */
function Str_random_hex(int $length): string
{
    return bin2hex(random_bytes($length / 2));
}
