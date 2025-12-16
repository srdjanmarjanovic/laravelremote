<?php

namespace App\Http\Requests;

use App\Services\HtmlSanitizer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isHR() || $this->user()->isAdmin();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize text fields - strip HTML
        if ($this->has('name')) {
            $this->merge(['name' => HtmlSanitizer::stripHtml($this->input('name'))]);
        }

        if ($this->has('description')) {
            $this->merge(['description' => HtmlSanitizer::stripHtml($this->input('description'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'], // 2MB max
            'website' => ['nullable', 'url', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'social_links.twitter' => ['nullable', 'url', 'max:255'],
            'social_links.linkedin' => ['nullable', 'url', 'max:255'],
            'social_links.github' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Company name is required.',
            'name.max' => 'Company name cannot exceed 255 characters.',
            'description.required' => 'Company description is required.',
            'description.max' => 'Company description cannot exceed 5000 characters.',
            'logo.image' => 'Logo must be an image.',
            'logo.mimes' => 'Logo must be JPEG, JPG, PNG, WEBP, or SVG.',
            'logo.max' => 'Logo file size cannot exceed 2MB.',
            'website.url' => 'Please provide a valid website URL.',
            'social_links.twitter.url' => 'Please provide a valid Twitter URL.',
            'social_links.linkedin.url' => 'Please provide a valid LinkedIn URL.',
            'social_links.github.url' => 'Please provide a valid GitHub URL.',
        ];
    }
}
