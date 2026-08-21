<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;

class UserSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Create settings for every user that doesn't already have them
        $users = User::doesntHave('settings')->get();

        foreach ($users as $user) {
            UserSetting::factory()
                ->egyptian()          // default to Cairo timezone
                ->create([
                    'user_id' => $user->id,
                ]);
        }
    }
}
