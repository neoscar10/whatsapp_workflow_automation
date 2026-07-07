<?php

namespace App\Services\WhatsApp\Simulation;

use App\Models\WhatsApp\WhatsAppSimulatedMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class SimulatedWhatsAppMediaResolver
{
    /**
     * Store a simulated upload and return its simulated media ID.
     */
    public function storeSimulatedUpload(UploadedFile $file, int $companyId, int $contactId, int $userId): string
    {
        if (!config('services.whatsapp.simulator.enabled') && app()->environment() !== 'local') {
            throw new Exception("WhatsApp Simulator is not enabled in this environment.");
        }

        $simulatedMediaId = 'sim_media_' . Str::random(16);
        $extension = $file->getClientOriginalExtension() ?: 'bin';
        $originalFilename = $file->getClientOriginalName() ?: ($simulatedMediaId . '.' . $extension);
        $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
        $fileSize = $file->getSize();

        // Path: whatsapp-simulator/{company_id}/{contact_id}/{sim_media_id}.{ext}
        $diskName = 'local';
        $targetDirectory = "whatsapp-simulator/{$companyId}/{$contactId}";
        $filename = "{$simulatedMediaId}.{$extension}";
        $storagePath = $file->storeAs($targetDirectory, $filename, $diskName);

        if (!$storagePath) {
            throw new Exception("Failed to store simulated media file.");
        }

        WhatsAppSimulatedMedia::create([
            'simulated_media_id' => $simulatedMediaId,
            'company_id' => $companyId,
            'contact_id' => $contactId,
            'uploaded_by' => $userId,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'file_size' => $fileSize,
            'storage_disk' => $diskName,
            'storage_path' => $storagePath,
        ]);

        return $simulatedMediaId;
    }

    /**
     * Check if a media ID is a simulated ID.
     */
    public function isSimulatedMediaId(string $mediaId): bool
    {
        return str_starts_with($mediaId, 'sim_media_') && config('services.whatsapp.simulator.enabled');
    }

    /**
     * Get simulated media details.
     */
    public function getSimulatedMedia(string $mediaId): ?WhatsAppSimulatedMedia
    {
        return WhatsAppSimulatedMedia::where('simulated_media_id', $mediaId)->first();
    }

    /**
     * Get binary contents of simulated media.
     */
    public function getMediaContents(string $mediaId): ?string
    {
        $media = $this->getSimulatedMedia($mediaId);
        if (!$media) {
            return null;
        }

        return Storage::disk($media->storage_disk)->get($media->storage_path);
    }
}
