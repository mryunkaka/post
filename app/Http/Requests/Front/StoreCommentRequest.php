<?php

namespace App\Http\Requests\Front;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
            'guest_name' => ['required_without:user_id', 'nullable', 'string', 'max:150'],
            'guest_email' => ['required_without:user_id', 'nullable', 'email', 'max:150'],
            'content' => ['required', 'string', 'max:3000'],
            'parent_id' => ['nullable', 'integer'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }
}
