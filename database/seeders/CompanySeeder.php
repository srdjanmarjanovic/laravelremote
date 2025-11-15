<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hrUsers = User::where('role', 'hr')->get();

        foreach ($hrUsers as $hrUser) {
            // Create 1-2 companies per HR user
            $companyCount = rand(1, 2);

            for ($i = 0; $i < $companyCount; $i++) {
                $company = Company::factory()->create([
                    'created_by_user_id' => $hrUser->id,
                ]);

                // Attach HR user as owner
                $company->users()->attach($hrUser->id, [
                    'role' => 'owner',
                    'invited_by' => null,
                    'joined_at' => now(),
                ]);

                // Optionally attach other HR users as members/admins
                if (rand(0, 1)) {
                    $otherHrUsers = $hrUsers->where('id', '!=', $hrUser->id)->random(min(2, $hrUsers->count() - 1));

                    foreach ($otherHrUsers as $otherUser) {
                        $company->users()->attach($otherUser->id, [
                            'role' => ['admin', 'member'][rand(0, 1)],
                            'invited_by' => $hrUser->id,
                            'joined_at' => now()->subDays(rand(1, 30)),
                        ]);
                    }
                }
            }
        }
    }
}
