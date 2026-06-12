<?php

namespace Modules\CA\Services;

use App\Models\User;
use App\Services\Contact\ContactService;
use Modules\CA\Models\CAClient;
use Modules\CA\Models\CAClientCompliance;
use Modules\CA\Events\ClientCreated;
use Modules\CA\Events\ClientUpdated;
use Modules\CA\Events\ClientDeleted;
use Modules\CA\Events\CompliancesAssigned;
use Illuminate\Support\Facades\DB;
use App\Support\PhoneNumberNormalizer;
use App\Models\Contact\Contact;
use Modules\CA\Services\RequirementSnapshotService;
use Modules\CA\Services\DeadlineService;
use Exception;

class CAClientService
{
    protected $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * Create a new CA Client and sync with the Contact module.
     */
    public function createClient(User $actor, array $data, ?int $businessTypeId = null): CAClient
    {
        return DB::transaction(function () use ($actor, $data, $businessTypeId) {
            // 1. Synchronize/Create Contact
            $contact = null;
            if (!empty($data['phone'])) {
                $normalizedPhone = PhoneNumberNormalizer::normalize($data['phone']);
                $contact = Contact::where('company_id', $actor->company_id)
                    ->where('normalized_phone', $normalizedPhone)
                    ->first();

                if ($contact) {
                    // Update notes if linking
                    $contact->update([
                        'notes' => trim($contact->notes . "\nLinked to CA Client Onboarding")
                    ]);
                } else {
                    $contact = $this->contactService->create($actor, [
                        'name' => $data['client_name'],
                        'phone' => $data['phone'],
                        'source' => 'ca_onboarding',
                        'notes' => 'Created via CA Client Onboarding'
                    ]);
                }
            }

            // 2. Create CA Client Record
            $client = CAClient::create([
                'company_id' => $actor->company_id,
                'contact_id' => $contact ? $contact->id : null,
                'ca_business_type_id' => $businessTypeId,
                'client_name' => $data['client_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'draft',
                'current_step' => 1,
                'onboarding_status' => 'in_progress',
                'created_by' => $actor->id,
            ]);

            // 3. Fire Event
            event(new ClientCreated($client));

            return $client;
        });
    }
    /**
     * Mark a draft client as completed.
     */
    public function completeOnboarding(User $actor, CAClient $client): void
    {
        if ($client->company_id !== $actor->company_id) {
            throw new Exception("Unauthorized access to client.");
        }

        $client->update([
            'status' => 'active',
            'onboarding_status' => 'completed',
            'onboarding_completed_at' => now(),
        ]);
    }
    /**
     * Update an existing CA Client
     */
    public function updateClient(User $actor, CAClient $client, array $data): CAClient
    {
        if ($client->company_id !== $actor->company_id) {
            throw new Exception("Unauthorized access to client.");
        }

        return DB::transaction(function () use ($actor, $client, $data) {
            $client->update($data);

            // Optionally sync contact updates if name/phone changed
            // Implementation for contact update omitted for brevity, keeping focus on CAClient
            
            event(new ClientUpdated($client));

            return $client;
        });
    }

    /**
     * Delete a CA Client
     */
    public function deleteClient(User $actor, CAClient $client): void
    {
        if ($client->company_id !== $actor->company_id) {
            throw new Exception("Unauthorized access to client.");
        }

        DB::transaction(function () use ($client) {
            $client->delete();
            event(new ClientDeleted($client));
        });
    }

    /**
     * Assign compliances to a client
     */
    public function assignCompliances(User $actor, CAClient $client, array $complianceIds): void
    {
        if ($client->company_id !== $actor->company_id) {
            throw new Exception("Unauthorized access to client.");
        }

        DB::transaction(function () use ($actor, $client, $complianceIds) {
            $snapshotService = app(RequirementSnapshotService::class);
            $deadlineService = app(DeadlineService::class);

            foreach ($complianceIds as $complianceId) {
                $clientCompliance = CAClientCompliance::updateOrCreate(
                    [
                        'ca_client_id' => $client->id,
                        'ca_compliance_id' => $complianceId,
                    ],
                    [
                        'status' => 'active',
                        'assigned_at' => now(),
                        'assigned_by' => $actor->id,
                    ]
                );

                // Phase 3: Snapshot Requirements and generate Deadlines
                $snapshotService->createSnapshot($clientCompliance, $actor);
                $deadlineService->generateDeadlines($clientCompliance);
            }

            event(new CompliancesAssigned($client, $complianceIds));
        });
    }
}
