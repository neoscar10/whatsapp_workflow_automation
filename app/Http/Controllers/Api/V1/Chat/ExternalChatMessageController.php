<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Chat\InjectChatMessageRequest;
use App\Http\Resources\Api\V1\Chat\ChatMessageResource;
use App\Http\Resources\Api\V1\Chat\ChatConversationResource;
use App\Services\Chat\ChatMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ExternalChatMessageController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected ChatMessageService $messageService
    ) {}

    /**
     * Inject / log an external chat message into the local database without broadcasting to Meta WhatsApp API.
     *
     * @param InjectChatMessageRequest $request
     * @return JsonResponse
     */
    public function injectMessage(InjectChatMessageRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->company_id) {
            return $this->errorResponse('Authenticated user does not belong to a company.', [], 400);
        }

        try {
            $message = $this->messageService->injectExternalMessage($user, $request->validated());

            $message->load(['conversation', 'sender']);

            return $this->successResponse([
                'message' => new ChatMessageResource($message),
                'conversation' => new ChatConversationResource($message->conversation->load('assignee')),
            ], 'External chat message injected and stored successfully.', 201);
        } catch (\Exception $e) {
            Log::error('API External Inject Chat Message Error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'company_id' => $user->company_id,
                'request' => $request->except(['file']),
            ]);

            return $this->errorResponse('Failed to inject external chat message: ' . $e->getMessage(), [], 500);
        }
    }
}
