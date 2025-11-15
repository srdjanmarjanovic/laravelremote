<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePositionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isHR() || $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:500'],
            'long_description' => ['required', 'string'],
            'company_id' => ['required', 'exists:companies,id'],
            'seniority' => ['nullable', Rule::in(['junior', 'mid', 'senior', 'lead', 'principal'])],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:9999999.99', 'gte:salary_min'],
            'remote_type' => ['required', Rule::in(['global', 'timezone', 'country'])],
            'location_restriction' => ['nullable', 'string', 'max:255', 'required_if:remote_type,timezone,country'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['boolean'],
            'is_external' => ['boolean'],
            'external_apply_url' => ['nullable', 'url', 'max:255', 'required_if:is_external,true'],
            'allow_platform_applications' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'technology_ids' => ['nullable', 'array'],
            'technology_ids.*' => ['exists:technologies,id'],
            'custom_questions' => ['nullable', 'array', 'max:10'],
            'custom_questions.*.question_text' => ['required', 'string', 'max:1000'],
            'custom_questions.*.is_required' => ['boolean'],
            'custom_questions.*.order' => ['nullable', 'integer', 'min:0'],
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
            'title.required' => 'The position title is required.',
            'short_description.required' => 'A short description is required.',
            'short_description.max' => 'The short description cannot exceed 500 characters.',
            'long_description.required' => 'A detailed description is required.',
            'company_id.required' => 'Please select a company.',
            'company_id.exists' => 'The selected company does not exist.',
            'salary_max.gte' => 'The maximum salary must be greater than or equal to the minimum salary.',
            'location_restriction.required_if' => 'Location restriction is required when remote type is timezone or country.',
            'external_apply_url.required_if' => 'External application URL is required when position is marked as external.',
            'external_apply_url.url' => 'Please provide a valid URL for the external application.',
            'expires_at.after' => 'The expiration date must be in the future.',
            'technology_ids.*.exists' => 'One or more selected technologies are invalid.',
            'custom_questions.max' => 'You can add up to 10 custom questions.',
            'custom_questions.*.question_text.required' => 'Question text is required for all custom questions.',
        ];
    }
}
