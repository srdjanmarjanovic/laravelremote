<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomQuestion>
 */
class CustomQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $questions = [
            'Why do you want to work for our company?',
            'What is your experience with remote work?',
            'Describe your experience with our tech stack.',
            'What are your salary expectations?',
            'When can you start?',
            'Do you have experience working in a distributed team?',
            'What timezone are you in?',
            'Tell us about a challenging project you worked on.',
            'What motivates you as a developer?',
            'How do you handle tight deadlines?',
        ];

        return [
            'position_id' => \App\Models\Position::factory(),
            'question_text' => fake()->randomElement($questions),
            'is_required' => fake()->boolean(60),
            'order' => 0,
        ];
    }
}
