<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Create a profile for every user that doesn't already have one
        $users = User::doesntHave('profile')->get();

        foreach ($users as $user) {
            Profile::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
