<?php

namespace Database\Factories;

use App\Models\AuthProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AuthProvider>
 */
class AuthProviderFactory extends Factory
{
    /** Available OAuth providers matching the migration enum */
    private const PROVIDERS = ['google', 'github', 'facebook', 'twitter', 'linkedin', 'instagram'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $provider = fake()->randomElement(self::PROVIDERS);

        return [
            'user_id'              => User::factory(),
            'provider_name'        => $provider,
            'provider_user_id'     => fake()->unique()->numerify('##########'),
            'provider_access_token'=> Str::random(64),
            'refresh_token'        => fake()->optional(0.6)->passthrough(Str::random(64)),
            'token_expires_at'     => fake()->optional(0.7)->dateTimeBetween('now', '+1 year'),
            'email'                => fake()->optional(0.9)->safeEmail(),
            'name'                 => fake()->optional(0.9)->name(),
            'avatar'               => fake()->optional(0.6)->imageUrl(200, 200, 'people'),
            'updated_by'           => null,
        ];
    }

    // ─── Named States (per provider) ─────────────────────────────────────────

    public function google(): static
    {
        return $this->state(fn () => ['provider_name' => 'google']);
    }

    public function github(): static
    {
        return $this->state(fn () => ['provider_name' => 'github']);
    }

    public function facebook(): static
    {
        return $this->state(fn () => ['provider_name' => 'facebook']);
    }

    public function twitter(): static
    {
        return $this->state(fn () => ['provider_name' => 'twitter']);
    }

    public function linkedin(): static
    {
        return $this->state(fn () => ['provider_name' => 'linkedin']);
    }

    public function instagram(): static
    {
        return $this->state(fn () => ['provider_name' => 'instagram']);
    }

    /** Token already expired */
    public function expired(): static
    {
        return $this->state(fn () => [
            'token_expires_at' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }
}
