<?php

namespace App\Http\Requests\Api\V1\WhatsApp\Setup;

use App\Http\Requests\Api\BaseApiRequest;

class UpdateWhatsAppAccountRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'access_token' => 'nullable|string',
            'waba_id' => 'sometimes|required|string',
            'business_id' => 'sometimes|required|string',
            'webhook_callback_url' => 'sometimes|nullable|url|max:2048',
            'display_name' => 'sometimes|nullable|string|max:255',
        ];
    }
}
