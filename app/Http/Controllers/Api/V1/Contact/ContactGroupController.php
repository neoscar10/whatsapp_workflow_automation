<?php

namespace App\Http\Controllers\Api\V1\Contact;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Resources\Api\V1\Contact\ContactGroupResource;
use App\Models\Contact\ContactGroup;
use App\Services\Contact\ContactGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactGroupController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected ContactGroupService $groupService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $groups = $this->groupService->listForCompany($request->user()->company_id);
        return $this->successResponse(ContactGroupResource::collection($groups), 'Groups retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100']);
        
        try {
            $group = $this->groupService->create($request->user(), $request->all());
            return $this->successResponse(new ContactGroupResource($group), 'Group created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:100']);

        try {
            $group = ContactGroup::where('company_id', $request->user()->company_id)->findOrFail($id);
            $group = $this->groupService->update($request->user(), $group, $request->all());
            return $this->successResponse(new ContactGroupResource($group), 'Group updated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $group = ContactGroup::where('company_id', $request->user()->company_id)->findOrFail($id);
            $this->groupService->delete($request->user(), $group);
            return $this->successResponse(null, 'Group deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }
}
