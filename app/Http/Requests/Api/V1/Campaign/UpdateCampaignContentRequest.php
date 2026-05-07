<?php

namespace App\Http\Requests\Api\V1\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignContentRequest extends FormRequest
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
        $rules = [
            'type' => 'required|in:template,text',
        ];

        if ($this->input('type') === 'template') {
            $rules['whatsapp_template_id'] = 'required|integer|exists:whatsapp_templates,id';
            $rules['template_variable_mapping'] = 'nullable|array';
            $rules['default_variable_values'] = 'nullable|array';
            $rules['personalization_config'] = 'nullable|array';
        } else {
            $rules['message_body'] = 'required|string';
        }

        return $rules;
    }
}
