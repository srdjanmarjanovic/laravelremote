<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Technology>
 */
class TechnologyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $technologies = [
            'Laravel', 'Vue.js', 'React', 'Node.js', 'PHP', 'Python', 'Ruby', 'Go',
            'TypeScript', 'JavaScript', 'MySQL', 'PostgreSQL', 'MongoDB', 'Redis',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'Tailwind CSS', 'Bootstrap',
            'Next.js', 'Nuxt.js', 'Express.js', 'Django', 'Flask', 'Ruby on Rails',
            'GraphQL', 'REST API', 'Microservices', 'Git', 'CI/CD', 'Jest', 'PHPUnit',
        ];

        $name = fake()->unique()->randomElement($technologies);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'icon' => null,
        ];
    }
}
