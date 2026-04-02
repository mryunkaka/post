<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_name' => ['required', 'string', 'max:150'],
            'site_description' => ['nullable', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'feature_amp_enabled' => ['nullable', 'boolean'],
            'feature_ai_enabled' => ['nullable', 'boolean'],
            'feature_comment_enabled' => ['nullable', 'boolean'],
        ];
    }
}
