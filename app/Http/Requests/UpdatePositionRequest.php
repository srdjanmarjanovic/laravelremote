<?php

namespace App\Http\Requests;

use App\Enums\ListingType;
use App\Services\HtmlSanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $position = $this->route('position');

        return $this->user()->canManagePosition($position);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize text fields - strip HTML from plain text fields
        if ($this->has('title')) {
            $this->merge(['title' => HtmlSanitizer::stripHtml($this->input('title'))]);
        }

        if ($this->has('short_description')) {
            $this->merge(['short_description' => HtmlSanitizer::stripHtml($this->input('short_description'))]);
        }

        // Sanitize rich text - allow safe HTML tags for position descriptions
        if ($this->has('long_description')) {
            $this->merge(['long_description' => HtmlSanitizer::sanitizeRichText($this->input('long_description'))]);
        }

        if ($this->has('location_restriction')) {
            $this->merge(['location_restriction' => HtmlSanitizer::stripHtml($this->input('location_restriction'))]);
        }

        // Sanitize custom questions
        if ($this->has('custom_questions')) {
            $questions = $this->input('custom_questions', []);
            foreach ($questions as $index => $question) {
                // Normalize is_required to proper boolean
                $questions[$index]['is_required'] = filter_var(
                    $question['is_required'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );
                // Strip HTML from question text
                if (isset($question['question_text'])) {
                    $questions[$index]['question_text'] = HtmlSanitizer::stripHtml($question['question_text']);
                }
            }
            $this->merge(['custom_questions' => $questions]);
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
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:500'],
            'long_description' => ['required', 'string'],
            'company_id' => ['required', 'exists:companies,id'],
            'seniority' => ['nullable', Rule::in(['junior', 'mid', 'senior', 'lead', 'principal'])],
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:9999999.99', 'gte:salary_min'],
            'remote_type' => ['required', Rule::in(['global', 'timezone', 'country'])],
            'location_restriction' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (in_array($this->input('remote_type'), ['timezone', 'country']) && empty($value)) {
                        $fail('Location restriction is required when remote type is timezone or country.');
                    }
                },
            ],
            // Admin can control status, expiration, and listing type; HR cannot
            'status' => ['nullable', Rule::in(['draft', 'published', 'expired', 'archived'])],
            'expires_at' => ['nullable', 'date'],
            'listing_type' => ['nullable', Rule::enum(ListingType::class)],
            'is_external' => ['boolean'],
            'external_apply_url' => ['nullable', 'url', 'max:255', 'required_if:is_external,true'],
            'allow_platform_applications' => ['boolean'],
            'technology_ids' => ['nullable', 'array'],
            'technology_ids.*' => ['exists:technologies,id'],
            'custom_questions' => ['nullable', 'array', 'max:10'],
            'custom_questions.*.id' => ['nullable', 'exists:custom_questions,id'],
            'custom_questions.*.question_text' => ['required', 'string', 'max:1000'],
            'custom_questions.*.is_required' => ['nullable', 'boolean'],
            'custom_questions.*.order' => ['nullable', 'integer', 'min:0'],
            'custom_questions.*._destroy' => ['nullable', 'boolean'],
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
            'technology_ids.*.exists' => 'One or more selected technologies are invalid.',
            'custom_questions.max' => 'You can add up to 10 custom questions.',
            'custom_questions.*.question_text.required' => 'Question text is required for all custom questions.',
        ];
    }
}
