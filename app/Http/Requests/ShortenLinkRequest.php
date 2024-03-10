<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShortenLinkRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'original_url' => 'required|url',
            'max_hits' => 'nullable|integer|min:0',
            'lifetime' => 'nullable|integer|min:1',
        ];
    }

    /**
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'original_url.required' => 'The original URL is required.',
            'original_url.url' => 'The original URL must be a valid URL.',
            'max_hits.integer' => 'Maximum hits must be an integer.',
            'max_hits.min' => 'Maximum hits cannot be negative.',
            'lifetime.integer' => 'Lifetime must be an integer.',
            'lifetime.min' => 'Lifetime must be at least 1 hour.',
        ];
    }
}
