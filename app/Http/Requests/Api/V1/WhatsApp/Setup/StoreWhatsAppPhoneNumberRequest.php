<?php

namespace App\Http\Requests\Api\V1\WhatsApp\Setup;

use App\Http\Requests\Api\BaseApiRequest;

class StoreWhatsAppPhoneNumberRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'display_name' => 'required|string|max:255',
            'phone_number_id' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:255',
        ];
    }
}
