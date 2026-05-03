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
            'file' => is_string($file) ? $file : (method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'unknown')
        ]);

        if (is_string($file)) {
            // Check if it's a storage path on the public disk first
            $realPath = Storage::disk('public')->exists($file) 
                ? Storage::disk('public')->path($file) 
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

    /**
     * Stage a media file for future sending (web-accessible staging).
     */
    public function stageMedia($file, array $options = []): array
    {
        $disk = $options['disk'] ?? 'public';
        $directory = $options['directory'] ?? 'staging_media';

        $originalName = method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : basename($file->getRealPath());
        $fileSize = $file->getSize();
        $fileMime = $file->getMimeType();

        // Move to public disk so it is web-accessible
        $filename = time() . '_' . $originalName;
        $stagedPath = $file->storeAs($directory, $filename, $disk);
        $stagedUrl = Storage::disk($disk)->url($stagedPath);

        return [
            'success' => true,
            'data' => [
                'name' => $originalName,
                'size' => $fileSize,
                'mime' => $fileMime,
                'staged_path' => $stagedPath,
                'staged_url' => $stagedUrl,
                'disk' => $disk,
                'preview_url' => str_starts_with($fileMime, 'image/') ? $stagedUrl : null,
                'media_type' => $this->getMediaTypeFromMime($fileMime),
            ],
            'message' => 'File staged successfully',
        ];
    }

    /**
     * Cleanup a staged media file.
     */
    public function cleanupStagedMedia(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }
        return false;
    }

    /**
     * Helper to determine media type from mime.
     */
    protected function getMediaTypeFromMime(string $mime): string
    {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';
        return 'document';
    }
}
