<?php

namespace App\Http\Requests\Api\V1\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMediaMessageRequest extends FormRequest
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
            'media_file' => [
                'required',
                'file',
                'max:16384', // 16MB limit matching Meta common limits
                'mimetypes:image/jpeg,image/png,video/mp4,video/3gpp,audio/aac,audio/amr,audio/mpeg,audio/ogg,application/pdf'
            ],
            'caption' => ['nullable', 'string', 'max:1024'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'media_file.required' => 'A media file is required before a WhatsApp media message can be sent.',
            'media_file.file' => 'The uploaded media must be a valid file.',
            'media_file.mimetypes' => 'The selected media file type is not supported for WhatsApp messages.',
            'media_file.max' => 'The selected media file is larger than the supported WhatsApp upload limit (16MB).',
            'caption.max' => 'WhatsApp media captions cannot exceed 1024 characters.',
        ];
    }
}
