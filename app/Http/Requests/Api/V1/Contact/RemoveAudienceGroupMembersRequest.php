<?php

namespace App\Http\Requests\Api\V1\Contact;

use Illuminate\Foundation\Http\FormRequest;

class RemoveAudienceGroupMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'required|integer|exists:contacts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'contact_ids.required' => 'Select at least one contact to remove from this audience group.',
            'contact_ids.min' => 'Select at least one contact to remove from this audience group.',
        ];
    }
}
