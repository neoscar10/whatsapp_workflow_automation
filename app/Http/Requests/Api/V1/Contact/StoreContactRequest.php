<?php

namespace App\Http\Requests\Api\V1\Contact;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:30',
            'whatsapp_phone_number_id' => 'nullable|integer|exists:whatsapp_phone_numbers,id',
            'status' => 'nullable|string|in:active,inactive,blocked,archived',
            'source' => 'nullable|string|max:50',
            'has_opted_in' => 'nullable|boolean',
            'opted_in_source' => 'nullable|string|max:100',
            'do_not_message' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:contact_tags,id',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:contact_groups,id',
        ];
    }
}
