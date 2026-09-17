<?php

namespace App\Http\Controllers\Api\V1\Contact;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Controllers\Api\Concerns\ResolvesCompanyContext;
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
    use RespondsWithApiResponse, ResolvesCompanyContext;

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

        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $contacts = $this->contactService->listForCompany($companyId, $filters);

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
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $contact = $this->contactService->create($request->user(), $request->validated(), $companyId);
            return $this->successResponse(new ContactResource($contact->load(['tags', 'groups'])), 'Contact created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Display the specified contact.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $contact = $this->contactService->findForCompany($companyId, $id);
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
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $contact = $this->contactService->findForCompany($companyId, $id);
            $contact = $this->contactService->update($request->user(), $contact, $request->validated());
            return $this->successResponse(new ContactResource($contact->load(['tags', 'groups'])), 'Contact updated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $contact = $this->contactService->findForCompany($companyId, $id);
            $this->contactService->delete($request->user(), $contact);
            return $this->successResponse(null, 'Contact deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Attach groups to a contact.
     */
    public function attachGroups(Request $request, int $id): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $request->validate([
            'group_ids' => 'required|array',
            'group_ids.*' => 'integer|exists:contact_groups,id',
        ]);

        try {
            $contact = $this->contactService->findForCompany($companyId, $id);
            $contact = $this->contactService->attachGroups($request->user(), $contact, $request->input('group_ids'));
            return $this->successResponse(new ContactResource($contact), 'Groups attached successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Detach a group from a contact.
     */
    public function detachGroup(Request $request, int $id, int $groupId): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $contact = $this->contactService->findForCompany($companyId, $id);
            $contact = $this->contactService->detachGroup($request->user(), $contact, $groupId);
            return $this->successResponse(new ContactResource($contact), 'Group detached successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Mark contact as opted in.
     */
    public function optIn(Request $request, int $id): JsonResponse
    {
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $contact = $this->contactService->findForCompany($companyId, $id);
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
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $contact = $this->contactService->findForCompany($companyId, $id);
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
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $user = $request->user();
        $isSuperAdmin = $user->role === 'super_admin' || ($user->is_super_admin ?? false);

        if (!$isSuperAdmin && !$user->is_company_owner) {
            return $this->errorResponse('Unauthorized.', [], 403);
        }

        $stats = $this->syncService->backfillFromConversations($companyId);

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
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            abort(403, 'User does not belong to a company.');
        }

        return response()->streamDownload(
            $this->exportService->exportToCsv($companyId),
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
