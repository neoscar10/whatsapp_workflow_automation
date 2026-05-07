<?php

namespace App\Http\Controllers\Api\V1\Contact;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Resources\Api\V1\Contact\ContactTagResource;
use App\Models\Contact\ContactTag;
use App\Services\Contact\ContactTagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactTagController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected ContactTagService $tagService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tags = $this->tagService->listForCompany($request->user()->company_id);
        return $this->successResponse(ContactTagResource::collection($tags), 'Tags retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50']);
        
        try {
            $tag = $this->tagService->create($request->user(), $request->all());
            return $this->successResponse(new ContactTagResource($tag), 'Tag created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:50']);

        try {
            $tag = ContactTag::where('company_id', $request->user()->company_id)->findOrFail($id);
            $tag = $this->tagService->update($request->user(), $tag, $request->all());
            return $this->successResponse(new ContactTagResource($tag), 'Tag updated successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $tag = ContactTag::where('company_id', $request->user()->company_id)->findOrFail($id);
            $this->tagService->delete($request->user(), $tag);
            return $this->successResponse(null, 'Tag deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }
}
