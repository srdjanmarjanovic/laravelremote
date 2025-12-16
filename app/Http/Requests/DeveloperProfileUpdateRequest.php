<?php

namespace App\Http\Requests;

use App\Services\HtmlSanitizer;
use Illuminate\Foundation\Http\FormRequest;

class DeveloperProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isDeveloper();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize summary - strip HTML
        if ($this->has('summary')) {
            $this->merge(['summary' => HtmlSanitizer::stripHtml($this->input('summary'))]);
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
            'summary' => ['required', 'string', 'max:2000'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'], // 5MB max
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'], // 2MB max
            'github_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:255'],
            'other_links' => ['nullable', 'array', 'max:5'],
            'other_links.*' => ['url', 'max:255'],
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
            'summary.required' => 'A professional summary is required.',
            'summary.max' => 'Your summary cannot exceed 2000 characters.',
            'cv.mimes' => 'Your CV must be a PDF, DOC, or DOCX file.',
            'cv.max' => 'Your CV file size cannot exceed 5MB.',
            'profile_photo.image' => 'Profile photo must be an image.',
            'profile_photo.mimes' => 'Profile photo must be JPEG, JPG, PNG, or WEBP.',
            'profile_photo.max' => 'Profile photo file size cannot exceed 2MB.',
            'github_url.url' => 'Please provide a valid GitHub URL.',
            'linkedin_url.url' => 'Please provide a valid LinkedIn URL.',
            'portfolio_url.url' => 'Please provide a valid portfolio URL.',
            'other_links.max' => 'You can add up to 5 additional links.',
            'other_links.*.url' => 'All links must be valid URLs.',
        ];
    }
}
