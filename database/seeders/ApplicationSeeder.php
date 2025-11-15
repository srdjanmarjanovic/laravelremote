<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class ApplicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $developers = User::where('role', 'developer')->get();
        $publishedPositions = Position::where('status', 'published')
            ->where('allow_platform_applications', true)
            ->where('is_external', false)
            ->get();

        foreach ($developers as $developer) {
            // Each developer applies to 1-5 positions
            $applicationCount = rand(1, 5);
            $positionsToApply = $publishedPositions->random(min($applicationCount, $publishedPositions->count()));

            foreach ($positionsToApply as $position) {
                $customAnswers = [];

                // Answer custom questions if any
                foreach ($position->customQuestions as $question) {
                    $customAnswers[$question->id] = fake()->paragraphs(rand(1, 3), true);
                }

                // Randomize application status
                $statusRand = rand(1, 100);
                if ($statusRand <= 50) {
                    $application = Application::factory()->pending()->create([
                        'position_id' => $position->id,
                        'user_id' => $developer->id,
                        'custom_answers' => $customAnswers,
                    ]);
                } elseif ($statusRand <= 70) {
                    $application = Application::factory()->reviewing()->create([
                        'position_id' => $position->id,
                        'user_id' => $developer->id,
                        'custom_answers' => $customAnswers,
                        'reviewed_by_user_id' => $position->created_by_user_id,
                    ]);
                } elseif ($statusRand <= 85) {
                    $application = Application::factory()->accepted()->create([
                        'position_id' => $position->id,
                        'user_id' => $developer->id,
                        'custom_answers' => $customAnswers,
                        'reviewed_by_user_id' => $position->created_by_user_id,
                    ]);
                } else {
                    $application = Application::factory()->rejected()->create([
                        'position_id' => $position->id,
                        'user_id' => $developer->id,
                        'custom_answers' => $customAnswers,
                        'reviewed_by_user_id' => $position->created_by_user_id,
                    ]);
                }
            }
        }
    }
}
