<?php

namespace App\Http\Requests\Api\V1\WhatsApp\Setup;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWhatsAppAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'access_token' => 'nullable|string',
            'waba_id' => 'required|string',
            'business_id' => 'required|string',
        ];
    }
}
