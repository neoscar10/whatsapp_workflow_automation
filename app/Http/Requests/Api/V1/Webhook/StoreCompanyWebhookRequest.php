<?php

namespace App\Http\Requests\Api\V1\Webhook;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyWebhookRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:message.received,message.status_update,template.status_update',
            'is_active' => 'boolean',
        ];
    }
}
