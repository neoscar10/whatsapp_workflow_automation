<?php

namespace App\Http\Requests\Api\V1\Company;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\DocumentType;

class UploadVerificationDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'document_type_id' => ['required', 'integer', 'exists:document_types,id'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
        ];

        // If we have a document_type_id, we can enforce specific file rules
        if ($this->has('document_type_id')) {
            $docType = DocumentType::find($this->document_type_id);
            if ($docType) {
                $maxSizeKb = $docType->max_size_mb * 1024;
                $formats = $docType->accepted_formats; // e.g. 'pdf,jpg,png,jpeg'
                $rules['file'] = ["required", "file", "max:{$maxSizeKb}", "mimes:{$formats}"];
            } else {
                $rules['file'] = ['required', 'file'];
            }
        } else {
            $rules['file'] = ['required', 'file'];
        }

        return $rules;
    }
}
