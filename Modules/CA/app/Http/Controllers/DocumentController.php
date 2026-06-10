<?php

namespace Modules\CA\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CA\Models\CADocument;
use Modules\CA\Services\DocumentService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Exception;

class DocumentController extends Controller
{
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    public function download(CADocument $document)
    {
        try {
            $path = $this->documentService->getSecurePath($document, Auth::user());
            
            return response()->download($path, $document->original_filename, [
                'Content-Type' => $document->mime_type ?? 'application/octet-stream'
            ]);
        } catch (Exception $e) {
            abort(403, $e->getMessage());
        }
    }
}
