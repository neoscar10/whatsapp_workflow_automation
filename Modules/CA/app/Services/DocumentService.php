<?php

namespace Modules\CA\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\CA\Models\CADocument;
use App\Models\User;
use Exception;

class DocumentService
{
    /**
     * Securely store an uploaded document
     */
    public function storeDocument(UploadedFile $file, User $uploader, array $data): CADocument
    {
        $companyId = $uploader->company_id;
        $clientId = $data['ca_client_id'] ?? 'general';
        
        // Extract metadata BEFORE store() moves/deletes the temporary Livewire file
        $originalFilename = method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : $file->getFilename();
        $mimeType = method_exists($file, 'getClientMimeType') ? $file->getClientMimeType() : null;
        $extension = method_exists($file, 'getClientOriginalExtension') ? $file->getClientOriginalExtension() : $file->getExtension();
        
        $fileSize = 0;
        try {
            $fileSize = $file->getSize();
        } catch (\Throwable $e) {
            $fileSize = 0;
        }

        // Private storage path: ca_documents/{company_id}/{client_id}
        $directory = "ca_documents/{$companyId}/{$clientId}";
        
        $path = $file->store($directory, 'local');

        if (!$path) {
            throw new Exception("Failed to store file.");
        }

        // If file_size wasn't retrieved prior to move, get it from the stored path on disk
        if (!$fileSize && Storage::disk('local')->exists($path)) {
            try {
                $fileSize = Storage::disk('local')->size($path);
            } catch (\Throwable $e) {
                $fileSize = 0;
            }
        }

        return CADocument::create([
            'company_id' => $companyId,
            'ca_client_id' => $data['ca_client_id'] ?? null,
            'ca_client_compliance_id' => $data['ca_client_compliance_id'] ?? null,
            'ca_client_compliance_requirement_id' => $data['ca_client_compliance_requirement_id'] ?? null,
            'ca_document_type_id' => $data['ca_document_type_id'] ?? null,
            'document_name' => $data['document_name'] ?? $originalFilename,
            'document_type' => $data['document_type'] ?? null,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'storage_disk' => 'local',
            'storage_path' => $path,
            'original_filename' => $originalFilename,
            'file_size' => $fileSize,
            'status' => 'uploaded',
            'uploaded_by' => $uploader->id,
        ]);
    }

    /**
     * Retrieve document file path securely
     */
    public function getSecurePath(CADocument $document, User $actor): string
    {
        if ($document->company_id !== $actor->company_id) {
            throw new Exception("Unauthorized to access this document.");
        }

        if (!Storage::disk($document->storage_disk)->exists($document->storage_path)) {
            throw new Exception("File not found on disk.");
        }

        return Storage::disk($document->storage_disk)->path($document->storage_path);
    }
}
