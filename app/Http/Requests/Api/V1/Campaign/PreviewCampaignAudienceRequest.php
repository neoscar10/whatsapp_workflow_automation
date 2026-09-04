<?php

namespace App\Http\Requests\Api\V1\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class PreviewCampaignAudienceRequest extends FormRequest
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
            'audience_type' => 'nullable|string',
            'type' => 'nullable|string',
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'integer|exists:contacts,id',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:contact_tags,id',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer|exists:contact_groups,id',
            'filters' => 'nullable|array',
            'filters.source' => 'nullable|string',
            'filters.status' => 'nullable|string',
            'filters.has_opted_in' => 'nullable|boolean',
            'filters.do_not_message' => 'nullable|boolean',
            'filters.tag_ids' => 'nullable|array',
            'filters.group_ids' => 'nullable|array',
            'filters.search' => 'nullable|string',
        ];
    }
}
