<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an authenticable ADMIN user
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create an authenticable HR user
        User::factory()->hr()->create([
            'name' => 'HR User',
            'email' => 'hr@example.com',
        ]);

        // Create an authenticable DEVELOPER user
        User::factory()->developer()->create([
            'name' => 'DEV User',
            'email' => 'dev@example.com',
        ]);

        // Create additional HR users
        User::factory()->hr()->count(5)->create();

        // Create additional developer users with profiles
        User::factory()
            ->developer()
            ->count(20)
            ->create()
            ->each(function ($user) {
                $user->developerProfile()->create([
                    'summary' => fake()->paragraphs(3, true),
                    'cv_path' => 'cvs/'.fake()->uuid().'.pdf',
                    'profile_photo_path' => 'profile-photos/'.fake()->uuid().'.jpg',
                    'github_url' => 'https://github.com/'.fake()->userName(),
                    'linkedin_url' => 'https://linkedin.com/in/'.fake()->userName(),
                    'portfolio_url' => fake()->url(),
                ]);
            });
    }
}
