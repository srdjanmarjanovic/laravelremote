<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $position = $this->route('position');

        return $this->user()
            && $this->user()->isDeveloper()
            && $this->user()->hasCompleteProfile()
            && $position->canReceiveApplications()
            && ! $position->applications()->where('user_id', $this->user()->id)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $position = $this->route('position');
        $rules = [
            'cover_letter' => ['nullable', 'string', 'max:5000'],
            'custom_answers' => ['nullable', 'array'],
        ];

        // Validate custom question answers
        if ($position && $position->customQuestions) {
            foreach ($position->customQuestions as $question) {
                $key = "custom_answers.{$question->id}";
                if ($question->is_required) {
                    $rules[$key] = ['required', 'string', 'max:2000'];
                } else {
                    $rules[$key] = ['nullable', 'string', 'max:2000'];
                }
            }
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
        return [
            'cover_letter.max' => 'Your cover letter cannot exceed 5000 characters.',
            'custom_answers.*.required' => 'This question is required.',
            'custom_answers.*.max' => 'Your answer cannot exceed 2000 characters.',
        ];
    }
}
