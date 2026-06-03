<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\CompanyVerificationDocumentVersion;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VerificationFileController extends Controller
{
    public function show($versionId)
    {
        $version = CompanyVerificationDocumentVersion::with('document.verification')->findOrFail($versionId);
        $user = Auth::user();

        // Authorize access
        if ($user->role !== 'super_admin' && $user->company_id !== $version->document->verification->company_id) {
            abort(403, 'Unauthorized access to verification document.');
        }

        if (!Storage::disk('local')->exists($version->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->response($version->file_path, $version->file_name);
    }

    public function downloadAll($verificationId)
    {
        $verification = \App\Models\CompanyVerification::with('company', 'documents.latestVersion')->findOrFail($verificationId);
        $user = Auth::user();

        // Authorize access
        if ($user->role !== 'super_admin' && $user->company_id !== $verification->company_id) {
            abort(403, 'Unauthorized access.');
        }

        // Get all latest versions of documents that are actually uploaded
        $files = [];
        foreach ($verification->documents as $doc) {
            if ($doc->latestVersion && Storage::disk('local')->exists($doc->latestVersion->file_path)) {
                $files[] = [
                    'path' => Storage::disk('local')->path($doc->latestVersion->file_path),
                    'name' => $doc->latestVersion->file_name,
                ];
            }
        }

        if (empty($files)) {
            return redirect()->back()->with('error', 'No documents uploaded yet.');
        }

        // Create ZipArchive
        $zip = new \ZipArchive();
        $cleanCompanyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $verification->company->name);
        $zipName = 'company_' . $cleanCompanyName . '_documents.zip';
        $tempFile = tempnam(sys_get_temp_dir(), 'zip');

        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $names = [];
            foreach ($files as $file) {
                $name = $file['name'];
                $base = pathinfo($name, PATHINFO_FILENAME);
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $counter = 1;
                while (in_array($name, $names)) {
                    $name = $base . '_' . $counter . '.' . $ext;
                    $counter++;
                }
                $names[] = $name;
                $zip->addFile($file['path'], $name);
            }
            $zip->close();
        } else {
            abort(500, 'Could not create ZIP file.');
        }

        return response()->download($tempFile, $zipName)->deleteFileAfterSend(true);
    }
}
