<?php

namespace App\Http\Controllers\Api\V1\Contact;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Contact\StoreContactRequest;
use App\Http\Requests\Api\V1\Contact\UpdateContactRequest;
use App\Http\Resources\Api\V1\Contact\ContactResource;
use App\Models\Contact\Contact;
use App\Http\Requests\Api\V1\Contact\ImportContactsRequest;
use App\Services\Contact\ContactService;
use App\Services\Contact\ContactSyncService;
use App\Services\Contact\ContactImportService;
use App\Services\Contact\ContactExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected ContactService $contactService,
        protected ContactSyncService $syncService,
        protected ContactImportService $importService,
        protected ContactExportService $exportService
    ) {}

    /**
     * Display a listing of contacts.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'status', 'source', 'tag_id', 'group_id', 
            'has_opted_in', 'do_not_message', 'per_page'
        ]);

        $contacts = $this->contactService->listForCompany($request->user()->company_id, $filters);

        return $this->successResponse(
            ContactResource::collection($contacts)->response()->getData(true),
            'Contacts retrieved successfully.'
        );
    }

    /**
     * Store a newly created contact.
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        try {
            $contact = $this->contactService->create($request->user(), $request->validated());
            return $this->successResponse(new ContactResource($contact), 'Contact created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Display the specified contact.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $contact = $this->contactService->findForCompany($request->user()->company_id, $id);
            return $this->successResponse(new ContactResource($contact->load(['tags', 'groups'])), 'Contact retrieved successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Contact not found.', [], 404);
        }
    }

    /**
     * Update the specified contact.
     */
    public function update(UpdateContactRequest $request, int $id): JsonResponse
    {
        try {
            $contact = $this->contactService->findForCompany($request->user()->company_id, $id);
            $contact = $this->contactService->update($request->user(), $contact, $request->validated());
            return $this->successResponse(new ContactResource($contact), 'Contact updated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $contact = $this->contactService->findForCompany($request->user()->company_id, $id);
            $this->contactService->delete($request->user(), $contact);
            return $this->successResponse(null, 'Contact deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Mark contact as opted in.
     */
    public function optIn(Request $request, int $id): JsonResponse
    {
        try {
            $contact = $this->contactService->findForCompany($request->user()->company_id, $id);
            $this->contactService->markOptedIn($request->user(), $contact, $request->source);
            return $this->successResponse(new ContactResource($contact->refresh()), 'Contact marked as opted in.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Mark contact as opted out.
     */
    public function optOut(Request $request, int $id): JsonResponse
    {
        try {
            $contact = $this->contactService->findForCompany($request->user()->company_id, $id);
            $this->contactService->markOptedOut($request->user(), $contact, $request->reason);
            return $this->successResponse(new ContactResource($contact->refresh()), 'Contact marked as opted out.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Sync contacts from existing conversations.
     */
    public function sync(Request $request): JsonResponse
    {
        // Only allow company owners to trigger sync via API?
        if (!$request->user()->is_company_owner) {
            return $this->errorResponse('Unauthorized.', [], 403);
        }

        $stats = $this->syncService->backfillFromConversations($request->user()->company_id);

        return $this->successResponse($stats, 'Sync completed successfully.');
    }

    /**
     * Import contacts from a CSV file.
     */
    public function import(ImportContactsRequest $request): JsonResponse
    {
        try {
            $stats = $this->importService->importFromCsv($request->user(), $request->file('file'));
            return $this->successResponse($stats, 'Contacts imported successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Export all contacts to a CSV file.
     */
    public function export(Request $request): StreamedResponse
    {
        return response()->streamDownload(
            $this->exportService->exportToCsv($request->user()->company_id),
            'contacts-export-' . now()->format('Y-m-d') . '.csv',
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }

    /**
     * Download CSV import template.
     */
    public function importTemplate(): StreamedResponse
    {
        return response()->streamDownload(
            $this->exportService->getImportTemplate(),
            'contacts-import-template.csv',
            [
                'Content-Type' => 'text/csv',
            ]
        );
    }
}
