<?php

namespace App\Http\Requests\Api\V1\WhatsApp\Setup;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWhatsAppPhoneNumberRequest extends FormRequest
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
