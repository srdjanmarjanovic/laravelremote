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
        $user = $this->user();

        if (! $user) {
            return false;
        }

        // Only developers and admins can apply
        if (! $user->isDeveloper() && ! $user->isAdmin()) {
            return false;
        }

        // Developers must have complete profile, admins don't need to
        if ($user->isDeveloper() && ! $user->hasCompleteProfile()) {
            return false;
        }

        // Check if position accepts applications
        if (! $position->canReceiveApplications()) {
            return false;
        }

        // Check if user already applied
        if ($position->applications()->where('user_id', $user->id)->exists()) {
            return false;
        }

        return true;
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
            'custom_answers.*.required' => 'This question is required.',
            'custom_answers.*.max' => 'Your answer cannot exceed 2000 characters.',
        ];
    }
}
