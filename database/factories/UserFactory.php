<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'username' => $username,
            'slug' => Str::slug($username).'-'.Str::random(5),
            'email' => fake()->unique()->safeEmail(),
            'mobile_number' => fake()->unique()->numerify('+201#########'),
            'national_id' => fake()->boolean(60)
                ? fake()->unique()->numerify('##############')
                : null,
            'nationality' => fake()->optional(0.7)->country(),
            'passport_number' => fake()->boolean(30)
                ? fake()->unique()->regexify('[A-Z]{1}\d{8}')
                : null,
            'password' => static::$password ??= Hash::make('password'),
            'status' => fake()->randomElement(UserStatus::cases())->value,
            'type' => UserType::USER->value,
            'credits' => fake()->numberBetween(0, 1000),
            'can_login' => true,
            'status_details' => null,
            'role_id' => null,
            'fcm_token' => null,
            'remember_token' => Str::random(10),
            'email_verified_at' => fake()->optional(0.8)->dateTimeThisYear(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    // ─── Named States ─────────────────────────────────────────────────────────

    /** Active, verified, can login */
    public function active(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::ACTIVE->value,
            'can_login' => true,
            'email_verified_at' => now(),
        ]);
    }

    /** Pending — newly registered, not yet verified */
    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::PENDING->value,
            'email_verified_at' => null,
        ]);
    }

    /** Suspended with a reason */
    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::SUSPENDED->value,
            'can_login' => false,
            'status_details' => fake()->sentence(),
        ]);
    }

    /** Banned */
    public function banned(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::BANNED->value,
            'can_login' => false,
            'status_details' => fake()->sentence(),
        ]);
    }

    /** Admin type */
    public function admin(): static
    {
        return $this->state(fn () => [
            'type' => UserType::ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
            'email_verified_at' => now(),
            'can_login' => true,
        ]);
    }

    /** Email not verified */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}
