<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\Chat\SendTextMessageRequest;
use App\Http\Requests\Api\V1\Chat\SendMediaMessageRequest;
use App\Http\Resources\Api\V1\Chat\ChatMessageResource;
use App\Services\Chat\ChatInboxService;
use App\Services\Chat\ChatMessageService;
use App\Services\WhatsApp\MetaMediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatMessageController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected ChatInboxService $inboxService,
        protected ChatMessageService $messageService,
        protected MetaMediaUploadService $mediaUploadService
    ) {}

    /**
     * Display a listing of messages for a conversation.
     *
     * @param Request $request
     * @param int $conversationId
     * @return JsonResponse
     */
    public function index(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $perPage = $request->query('per_page', 30);

        $conversation = $this->inboxService->getActiveConversationForUser($user, $conversationId);
        if (!$conversation) {
            return $this->errorResponse('Conversation not found for your company.', [], 404);
        }

        $messages = $conversation->messages()
            ->with(['sender'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->successResponse(
            ChatMessageResource::collection($messages)->response()->getData(true),
            'Conversation messages retrieved successfully.'
        );
    }

    /**
     * Send a text message.
     *
     * @param SendTextMessageRequest $request
     * @param int $conversationId
     * @return JsonResponse
     */
    public function sendText(SendTextMessageRequest $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        try {
            $message = $this->messageService->sendTextMessage($user, $conversationId, $request->message);
            
            if (!$message) {
                return $this->errorResponse('Conversation not found for your company.', [], 404);
            }

            return $this->successResponse(new ChatMessageResource($message), 'Text message sent successfully.');
        } catch (\Exception $e) {
            Log::error('API Send Text Message Error', ['error' => $e->getMessage(), 'conversation_id' => $conversationId]);
            return $this->errorResponse('WhatsApp message provider rejected the request. Check recipient number or account configuration.', [], 500);
        }
    }

    /**
     * Send a media message.
     *
     * @param SendMediaMessageRequest $request
     * @param int $conversationId
     * @return JsonResponse
     */
    public function sendMedia(SendMediaMessageRequest $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $file = $request->file('media_file');
        
        try {
            // 1. Stage the media using the centralized service
            $stagingResult = $this->mediaUploadService->stageMedia($file);
            
            if (!$stagingResult['success']) {
                return $this->errorResponse('Unable to stage the media file for WhatsApp delivery.', [], 500);
            }

            $stagedData = $stagingResult['data'];

            // 2. Send via message service
            $message = $this->messageService->sendMediaMessage(
                $user,
                $conversationId,
                $stagedData['staged_path'],
                [
                    'name' => $stagedData['name'],
                    'mime' => $stagedData['mime'],
                    'size' => $stagedData['size'],
                ],
                $request->caption
            );

            if (!$message) {
                // Cleanup staged if conversation not found
                $this->mediaUploadService->cleanupStagedMedia($stagedData['staged_path']);
                return $this->errorResponse('Conversation not found for your company.', [], 404);
            }

            return $this->successResponse(new ChatMessageResource($message), 'Media message sent successfully.');

        } catch (\Exception $e) {
            Log::error('API Send Media Message Error', ['error' => $e->getMessage(), 'conversation_id' => $conversationId]);
            return $this->errorResponse('Failed to send media message: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Mark conversation as read.
     *
     * @param Request $request
     * @param int $conversationId
     * @return JsonResponse
     */
    public function markRead(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $this->messageService->markConversationRead($user, $conversationId);
        return $this->successResponse(null, 'Conversation marked as read.');
    }
}
