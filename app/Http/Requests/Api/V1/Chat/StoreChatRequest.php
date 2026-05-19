<?php

namespace App\Http\Requests\Api\V1\Chat;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
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
            'contact_id' => [
                'required',
                'integer',
                // Make sure the contact belongs to the current user's company
                function ($attribute, $value, $fail) {
                    $exists = \App\Models\Contact\Contact::where('id', $value)
                        ->where('company_id', $this->user()->company_id)
                        ->exists();

                    if (!$exists) {
                        $fail('The selected contact is invalid or does not belong to your company.');
                    }
                },
            ]
        ];
    }
}
