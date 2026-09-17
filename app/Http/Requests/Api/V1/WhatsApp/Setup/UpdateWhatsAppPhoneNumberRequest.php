<?php

namespace App\Http\Requests\Api\V1\WhatsApp\Setup;

use App\Http\Requests\Api\BaseApiRequest;

class UpdateWhatsAppPhoneNumberRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => 'sometimes|required|string|max:255',
            'phone_number_id' => 'sometimes|required|string|max:255',
            'phone_number' => 'nullable|string|max:255',
        ];
    }
}
