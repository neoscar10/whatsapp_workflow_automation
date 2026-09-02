<?php

namespace App\Http\Requests\Api\V1\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class ManualCampaignRecipientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.phone' => ['required', 'string'],
            'rows.*.name' => ['nullable', 'string', 'max:255'],
            'rows.*.variables' => ['nullable', 'array'],
        ];
    }
}
