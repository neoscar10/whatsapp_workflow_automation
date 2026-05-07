<?php

namespace App\Http\Requests\Api\V1\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:template,text',
            'whatsapp_phone_number_id' => 'sometimes|integer|exists:whatsapp_phone_numbers,id',
            'scheduled_at' => 'nullable|date|after:now',
        ];
    }
}
