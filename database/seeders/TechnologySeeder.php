<?php

namespace Database\Seeders;

use App\Models\Technology;
use Illuminate\Database\Seeder;

class TechnologySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $technologies = [
            'PHP',
            'Laravel',
            'JavaScript',
            'TypeScript',
            'Vue.js',
            'React',
            'Node.js',
            'Python',
            'Django',
            'Ruby',
            'Ruby on Rails',
            'Go',
            'Rust',
            'Java',
            'Spring Boot',
            'Kotlin',
            'Swift',
            'C#',
            '.NET',
            'PostgreSQL',
            'MySQL',
            'MongoDB',
            'Redis',
            'Docker',
            'Kubernetes',
            'AWS',
            'Azure',
            'GCP',
            'Git',
            'CI/CD',
        ];

        foreach ($technologies as $tech) {
            Technology::create([
                'name' => $tech,
                'slug' => \Illuminate\Support\Str::slug($tech),
            ]);
        }
    }
}
