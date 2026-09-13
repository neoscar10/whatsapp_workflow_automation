<?php

namespace App\Http\Requests\Api\V1\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVerificationDetailsRequest extends FormRequest
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
        return [
            'legal_name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'signatory_name' => ['required', 'string', 'max:255'],
            'signatory_designation' => ['required', 'string', 'max:255'],
            'business_email' => ['required', 'email', 'max:255'],
            'business_phone' => ['required', 'string', 'max:50'],
            'wa_phone' => ['required', 'string', 'max:50'],
        ];
    }
}
