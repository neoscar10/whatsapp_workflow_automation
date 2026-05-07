<?php

namespace App\Http\Requests\Api\V1\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:template,text',
            'whatsapp_phone_number_id' => 'required|integer|exists:whatsapp_phone_numbers,id',
            'scheduled_at' => 'nullable|date|after:now',
            'audience_type' => 'nullable|in:selected_contacts,tags,groups,filters,imported,mixed',
        ];
    }
}
