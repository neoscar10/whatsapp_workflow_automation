<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\Chat\ConversationMessage;
use App\Services\WhatsApp\WhatsAppGraphClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatMediaProxyController extends Controller
{
    public function __construct(
        protected WhatsAppGraphClient $graphClient
    ) {}

    /**
     * Proxy an inbound WhatsApp media file via the Meta Graph API.
     * Fetches and streams the file so the browser can display it.
     */
    public function show(Request $request, int $messageId)
    {
        // Authenticate — must be logged in
        if (!$request->user()) {
            abort(401);
        }

        $message = ConversationMessage::with([
            'conversation.whatsappPhoneNumber.account'
        ])->findOrFail($messageId);

        // Ensure the user belongs to the same company as this conversation
        $companyId = $message->conversation->company_id;
        if ($request->user()->company_id !== $companyId) {
            abort(403);
        }

        $mediaId = $message->media_meta['media_id'] ?? null;
        if (!$mediaId) {
            abort(404, 'No media ID associated with this message.');
        }

        $accessToken = $message->conversation->whatsappPhoneNumber->account->access_token ?? null;
        if (!$accessToken) {
            Log::error("ChatMediaProxy: No access token for message", ['message_id' => $messageId]);
            abort(500, 'Missing WhatsApp access token.');
        }

        // 1. Get the direct download URL from Meta
        $mediaInfo = $this->graphClient->getMedia($mediaId, $accessToken);
        if (!$mediaInfo['success']) {
            Log::error("ChatMediaProxy: getMedia failed", [
                'message_id' => $messageId,
                'media_id' => $mediaId,
                'error' => $mediaInfo['error'] ?? 'unknown',
            ]);
            abort(502, 'Failed to retrieve media from WhatsApp: ' . ($mediaInfo['error'] ?? 'unknown'));
        }

        // 2. Download the binary content
        $fileContents = $this->graphClient->downloadMediaFile($mediaInfo['url'], $accessToken);
        if (!$fileContents) {
            Log::error("ChatMediaProxy: download returned empty", ['media_id' => $mediaId]);
            abort(502, 'Failed to download media content from WhatsApp.');
        }

        $mimeType = $mediaInfo['mime_type'] ?? ($message->media_meta['mime_type'] ?? 'application/octet-stream');
        $filename = $message->media_meta['filename'] ?? 'media';

        return response($fileContents, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=3600');
    }
}
