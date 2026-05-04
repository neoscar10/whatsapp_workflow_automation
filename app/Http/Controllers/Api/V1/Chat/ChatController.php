<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Chat\ListChatsRequest;
use App\Http\Resources\Api\V1\Chat\ChatConversationResource;
use App\Models\Chat\Conversation;
use App\Services\Chat\ChatInboxService;
use App\Services\Chat\ChatConversationActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected ChatInboxService $inboxService,
        protected ChatConversationActionService $actionService
    ) {}

    /**
     * Display a listing of chat conversations.
     *
     * @param ListChatsRequest $request
     * @return JsonResponse
     */
    public function index(ListChatsRequest $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->validated();
        $perPage = $filters['per_page'] ?? 15;

        // We use the same filter logic as the web inbox but with pagination
        $query = Conversation::where('company_id', $user->company_id)
            ->with(['assignee'])
            ->orderBy('last_message_at', 'desc');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('contact_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('contact_phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['tab'])) {
            if ($filters['tab'] === 'assigned') {
                $query->where('assignment_status', 'assigned')
                      ->where('assigned_user_id', $user->id);
            } elseif ($filters['tab'] === 'unassigned') {
                $query->where('assignment_status', 'unassigned');
            }
        }

        $conversations = $query->paginate($perPage);

        return $this->successResponse(
            ChatConversationResource::collection($conversations)->response()->getData(true),
            'Chat conversations retrieved successfully.'
        );
    }

    /**
     * Display the specified chat conversation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $conversation = $this->inboxService->getActiveConversationForUser($user, $id);

        if (!$conversation) {
            return $this->errorResponse('Conversation not found for your company.', [], 404);
        }

        return $this->successResponse(
            new ChatConversationResource($conversation->load(['assignee'])),
            'Conversation retrieved successfully.'
        );
    }

    /**
     * Close the specified conversation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function close(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        try {
            $this->actionService->closeConversation($user, $id);
            return $this->successResponse(null, 'Conversation closed successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to close conversation: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Reopen the specified conversation.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function reopen(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        try {
            $this->actionService->reopenConversation($user, $id);
            return $this->successResponse(null, 'Conversation reopened successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to reopen conversation: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Assign the conversation to another agent.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        $user = $request->user();
        try {
            $summary = $this->actionService->assignConversation($user, $id, $request->user_id);
            return $this->successResponse($summary, 'Conversation assigned successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), [], 422);
        }
    }
}
