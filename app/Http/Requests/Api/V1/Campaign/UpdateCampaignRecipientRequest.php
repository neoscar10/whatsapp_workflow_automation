<?php

namespace App\Http\Requests\Api\V1\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRecipientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'variables' => ['nullable', 'array'],
        ];
    }
}
