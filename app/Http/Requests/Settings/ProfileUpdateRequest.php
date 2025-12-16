<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];

        // Add developer profile validation rules if user is a developer
        if ($this->user()->isDeveloper()) {
            $rules['developer_profile.summary'] = ['required', 'string', 'max:2000'];
            $rules['developer_profile.cv'] = ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120']; // 5MB max
            $rules['developer_profile.profile_photo'] = ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048']; // 2MB max
            $rules['developer_profile.github_url'] = ['nullable', 'url', 'max:255'];
            $rules['developer_profile.linkedin_url'] = ['nullable', 'url', 'max:255'];
            $rules['developer_profile.portfolio_url'] = ['nullable', 'url', 'max:255'];
            $rules['developer_profile.other_links'] = ['nullable', 'array', 'max:5'];
            $rules['developer_profile.other_links.*'] = ['url', 'max:255'];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $messages = [];

        if ($this->user()->isDeveloper()) {
            $messages = [
                'developer_profile.summary.required' => 'A professional summary is required.',
                'developer_profile.summary.max' => 'Your summary cannot exceed 2000 characters.',
                'developer_profile.cv.mimes' => 'Your CV must be a PDF, DOC, or DOCX file.',
                'developer_profile.cv.max' => 'Your CV file size cannot exceed 5MB.',
                'developer_profile.profile_photo.image' => 'Profile photo must be an image.',
                'developer_profile.profile_photo.mimes' => 'Profile photo must be JPEG, JPG, PNG, or WEBP.',
                'developer_profile.profile_photo.max' => 'Profile photo file size cannot exceed 2MB.',
                'developer_profile.github_url.url' => 'Please provide a valid GitHub URL.',
                'developer_profile.linkedin_url.url' => 'Please provide a valid LinkedIn URL.',
                'developer_profile.portfolio_url.url' => 'Please provide a valid portfolio URL.',
                'developer_profile.other_links.max' => 'You can add up to 5 additional links.',
                'developer_profile.other_links.*.url' => 'All links must be valid URLs.',
            ];
        }

        return $messages;
    }
}
