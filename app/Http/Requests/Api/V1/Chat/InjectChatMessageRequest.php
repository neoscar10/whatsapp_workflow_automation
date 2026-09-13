<?php

namespace App\Http\Requests\Api\V1\Chat;

use Illuminate\Foundation\Http\FormRequest;

class InjectChatMessageRequest extends FormRequest
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
            'phone_number' => ['required', 'string', 'min:5', 'max:30'],
            'whatsapp_phone_number_id' => ['nullable', 'integer', 'exists:whatsapp_phone_numbers,id'],
            'direction' => ['nullable', 'string', 'in:outbound,inbound'],
            'message_type' => ['nullable', 'string', 'in:text,template,image,video,audio,document,other'],
            'type' => ['nullable', 'string', 'in:text,template,image,video,audio,document,other'],
            'body' => ['nullable', 'string', 'max:65535'],
            'message' => ['nullable', 'string', 'max:65535'],
            'template_name' => ['nullable', 'string', 'max:255'],
            'template_id' => ['nullable', 'exists:whatsapp_templates,id'],
            'components' => ['nullable', 'array'],
            'parameters' => ['nullable', 'array'],
            'media_url' => ['nullable', 'string', 'max:2048'],
            'media_filename' => ['nullable', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:100'],
            'file_size' => ['nullable', 'integer'],
            'external_message_id' => ['nullable', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'meta_payload' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Accept phone or customer_phone if phone_number is not explicitly provided
        if (!$this->has('phone_number')) {
            if ($this->has('phone')) {
                $this->merge(['phone_number' => $this->phone]);
            } elseif ($this->has('customer_phone')) {
                $this->merge(['phone_number' => $this->customer_phone]);
            }
        }

        // Standardize type / message_type
        if (!$this->has('message_type') && $this->has('type')) {
            $this->merge(['message_type' => $this->type]);
        }

        // Standardize body / message
        if (!$this->has('body') && $this->has('message')) {
            $this->merge(['body' => $this->message]);
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone_number.required' => 'The target user phone number is required.',
            'direction.in' => 'The direction field must be either "outbound" or "inbound".',
            'message_type.in' => 'Invalid message type. Allowed types: text, template, image, video, audio, document.',
        ];
    }
}
