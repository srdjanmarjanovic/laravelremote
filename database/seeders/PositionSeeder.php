<?php

namespace Database\Seeders;

use App\Enums\ListingType;
use App\Models\Company;
use App\Models\Position;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        $technologies = Technology::all();
        $hrUsers = User::where('role', 'hr')->get();

        foreach ($companies as $company) {
            // Get HR users associated with this company
            $companyHrUsers = $company->users;

            if ($companyHrUsers->isEmpty()) {
                // Fallback to any HR user
                $creator = $hrUsers->random();
            } else {
                $creator = $companyHrUsers->random();
            }

            // Create 3-8 positions per company
            $positionCount = rand(3, 8);

            for ($i = 0; $i < $positionCount; $i++) {
                // Decide position status
                $statusRand = rand(1, 100);
                if ($statusRand <= 70) {
                    // Published position - randomly assign listing_type to mix featured/regular by published_at
                    $listingTypeRand = rand(1, 100);
                    $listingType = match (true) {
                        $listingTypeRand <= 5 => ListingType::Top,      // ~5% top
                        $listingTypeRand <= 20 => ListingType::Featured, // ~15% featured
                        default => ListingType::Regular,                  // ~80% regular
                    };

                    $position = Position::factory()->published()->create([
                        'company_id' => $company->id,
                        'created_by_user_id' => $creator->id,
                        'listing_type' => $listingType,
                    ]);
                } elseif ($statusRand <= 85) {
                    $position = Position::factory()->draft()->create([
                        'company_id' => $company->id,
                        'created_by_user_id' => $creator->id,
                    ]);
                } else {
                    $position = Position::factory()->expired()->create([
                        'company_id' => $company->id,
                        'created_by_user_id' => $creator->id,
                    ]);
                }

                // Attach 2-6 random technologies
                $position->technologies()->attach(
                    $technologies->random(rand(2, 6))->pluck('id')
                );

                // Add 0-3 custom questions
                $questionCount = rand(0, 3);
                for ($j = 0; $j < $questionCount; $j++) {
                    $position->customQuestions()->create([
                        'question_text' => fake()->sentence().'?',
                        'is_required' => rand(0, 1),
                        'order' => $j,
                    ]);
                }

                // Add some position views for published positions
                if ($position->isPublished()) {
                    $viewCount = rand(10, 200);
                    for ($v = 0; $v < $viewCount; $v++) {
                        $position->views()->create([
                            'ip_address_hash' => hash('sha256', fake()->ipv4()),
                            'country_code' => fake()->countryCode(),
                            'city' => fake()->city(),
                            'user_agent' => fake()->userAgent(),
                            'referrer' => fake()->url(),
                            'viewed_at' => now()->subDays(rand(0, 30)),
                        ]);
                    }
                }
            }
        }
    }
}
