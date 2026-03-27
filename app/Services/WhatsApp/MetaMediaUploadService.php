<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class MetaMediaUploadService
{
    public function __construct(
        protected WhatsAppGraphClient $graphClient
    ) {}

    /**
     * Upload a sample media file for template creation review.
     * Returns a handle 'h' required for Meta template creation.
     */
    public function uploadTemplateSample(string $accessToken, string $appId, $file): string
    {
        Log::info("Initiating Template Sample Upload to Meta", [
            'file' => $file->getClientOriginalName(),
            'size' => $file->getSize()
        ]);

        // 1. Create Resumable Upload Session
        $sessionResult = $this->graphClient->createResumableUpload(
            $accessToken,
            $appId,
            $file->getSize(),
            $file->getMimeType()
        );

        if (!$sessionResult['success']) {
            throw new Exception("Meta Upload Session Failed: " . $sessionResult['error']);
        }

        $sessionId = $sessionResult['upload_session_id'];

        // 2. Upload File Content
        $uploadResult = $this->graphClient->uploadFileToSession(
            $accessToken,
            $sessionId,
            file_get_contents($file->getRealPath())
        );

        if (!$uploadResult['success']) {
            throw new Exception("Meta File Data Upload Failed: " . $uploadResult['error']);
        }

        Log::info("Template Sample Upload Successful", ['handle' => $uploadResult['h']]);

        return $uploadResult['h'];
    }

    /**
     * Upload a real media file for message delivery.
     * Returns a media ID.
     */
    public function uploadMessageMedia(string $phoneNumberId, string $accessToken, $file): string
    {
        Log::info("Initiating Message Media Upload to Meta", [
            'phone_id' => $phoneNumberId,
            'file' => is_string($file) ? $file : $file->getClientOriginalName()
        ]);

        if (is_string($file)) {
            // Check if it's a storage path on the public disk first
            $realPath = \Illuminate\Support\Facades\Storage::disk('public')->exists($file) 
                ? \Illuminate\Support\Facades\Storage::disk('public')->path($file) 
                : $file;
            $fileContents = file_get_contents($realPath);
            $filename = basename($realPath);
            $mimeType = mime_content_type($realPath);
        } else {
            $fileContents = file_get_contents($file->getRealPath());
            $filename = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
        }

        $result = $this->graphClient->uploadMessageMedia(
            $phoneNumberId,
            $accessToken,
            $fileContents,
            $filename,
            $mimeType
        );

        if (!$result['success']) {
            throw new Exception("Meta Message Media Upload Failed: " . $result['error']);
        }

        return $result['media_id'];
    }
}
