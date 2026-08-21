<?php

namespace Database\Seeders;

use App\Models\AuthProvider;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuthProviderSeeder extends Seeder
{
    public function run(): void
    {
        // Give ~30% of users a social login provider
        User::inRandomOrder()
            ->limit((int) (User::count() * 0.3))
            ->get()
            ->each(function (User $user) {
                // Each selected user gets 1 provider (rarely 2)
                $count = fake()->randomElement([1, 1, 1, 2]);

                AuthProvider::factory()
                    ->count($count)
                    ->create(['user_id' => $user->id]);
            });
    }
}
