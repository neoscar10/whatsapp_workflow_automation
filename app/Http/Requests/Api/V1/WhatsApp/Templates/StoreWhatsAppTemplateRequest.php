<?php

namespace App\Http\Requests\Api\V1\WhatsApp\Templates;

use Illuminate\Foundation\Http\FormRequest;

class StoreWhatsAppTemplateRequest extends FormRequest
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
            'name' => 'required|string|regex:/^[a-z0-9_]+$/|max:512',
            'category' => 'required|in:marketing,utility,authentication',
            'language_code' => 'required|string',
            'header_type' => 'required|in:none,text,image,video,document',
            'header_text' => 'required_if:header_type,text|nullable|string|max:60',
            'header_sample_file' => 'required_if:header_type,image,video,document|nullable|file|max:16384',
            'body_text' => 'required|string|max:1024',
            'footer_text' => 'nullable|string|max:60',
            'buttons' => 'nullable|array|max:10',
            'buttons.*.type' => 'required|in:quick_reply,url,phone_number',
            'buttons.*.text' => 'required|string|max:25',
            'buttons.*.url' => 'required_if:buttons.*.type,url|nullable|url|max:2000',
            'buttons.*.phone_number' => 'required_if:buttons.*.type,phone_number|nullable|string|max:20',
            'buttons.*.example_value' => 'nullable|string',
            'example_payload' => 'nullable|array',
            'example_payload.header_text' => 'nullable|array',
            'example_payload.body_text' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The template name can only contain lowercase letters, numbers, and underscores.',
        ];
    }
}
