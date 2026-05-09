<?php

namespace App\Http\Controllers\Api\V1\Contact;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Contact\AddAudienceGroupMembersRequest;
use App\Http\Requests\Api\V1\Contact\RemoveAudienceGroupMembersRequest;
use App\Http\Resources\Api\V1\Contact\ContactResource;
use App\Models\Contact\ContactGroup;
use App\Services\Contact\ContactGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AudienceGroupMemberController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected ContactGroupService $groupService
    ) {}

    /**
     * Search contacts that can be added to a group.
     */
    public function availableContacts(Request $request, int $groupId): JsonResponse
    {
        try {
            $group = ContactGroup::where('company_id', $request->user()->company_id)->findOrFail($groupId);
            
            $filters = $request->only(['search', 'per_page']);
            $contacts = $this->groupService->searchAvailableContactsForGroup($request->user(), $group, $filters);

            return $this->successResponse(
                ContactResource::collection($contacts)->response()->getData(true),
                'Available contacts retrieved successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Audience group not found for your company.', [], 404);
        }
    }

    /**
     * List current members of a group.
     */
    public function members(Request $request, int $groupId): JsonResponse
    {
        try {
            $group = ContactGroup::where('company_id', $request->user()->company_id)->findOrFail($groupId);
            
            $filters = $request->only(['search', 'per_page']);
            $members = $this->groupService->getGroupMembers($request->user(), $group, $filters);

            return $this->successResponse(
                ContactResource::collection($members)->response()->getData(true),
                'Audience group members retrieved successfully.'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Audience group not found for your company.', [], 404);
        }
    }

    /**
     * Add multiple contacts to a group.
     */
    public function storeMembers(AddAudienceGroupMembersRequest $request, int $groupId): JsonResponse
    {
        try {
            $group = ContactGroup::where('company_id', $request->user()->company_id)->findOrFail($groupId);
            
            $result = $this->groupService->addContactsToGroup(
                $request->user(), 
                $group, 
                $request->validated()['contact_ids']
            );

            if ($result['added_count'] === 0 && $result['skipped_existing_count'] > 0 && $result['invalid_count'] === 0) {
                 return $this->errorResponse('All selected contacts are already members of this audience group.', [
                    'contact_ids' => ['All selected contacts are already members of this audience group.']
                 ], 422);
            }

            return $this->successResponse($result, 'Contacts added to audience group successfully.');
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->errorResponse('Audience group not found for your company.', [], 404);
            }
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    /**
     * Remove multiple contacts from a group.
     */
    public function destroyMembers(RemoveAudienceGroupMembersRequest $request, int $groupId): JsonResponse
    {
        try {
            $group = ContactGroup::where('company_id', $request->user()->company_id)->findOrFail($groupId);
            
            $result = $this->groupService->removeContactsFromGroup(
                $request->user(), 
                $group, 
                $request->validated()['contact_ids']
            );

            return $this->successResponse($result, 'Contacts removed from audience group successfully.');
        } catch (\Exception $e) {
             if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->errorResponse('Audience group not found for your company.', [], 404);
            }
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }
}
