<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'first_name'  => fake()->firstName(),
            'middle_name' => fake()->optional(0.4)->firstName(),
            'last_name'   => fake()->lastName(),
            'whatsapp'    => fake()->optional(0.5)->numerify('+201#########'),
            'telegram'    => fake()->optional(0.3)->userName(),
            'date_of_birth' => fake()->optional(0.7)->dateTimeBetween('-60 years', '-18 years')?->format('Y-m-d'),
            'gender'      => fake()->optional(0.9)->randomElement(['male', 'female']),
            'avatar'      => fake()->optional(0.4)->imageUrl(200, 200, 'people'),
            'title'       => fake()->optional(0.3)->jobTitle(),
            'address'     => fake()->optional(0.5)->address(),
            'note'        => fake()->optional(0.2)->paragraph(),
            'created_by'  => null,
            'updated_by'  => null,
        ];
    }

    /** Male profile */
    public function male(): static
    {
        return $this->state(fn () => [
            'first_name' => fake()->firstNameMale(),
            'gender'     => 'male',
        ]);
    }

    /** Female profile */
    public function female(): static
    {
        return $this->state(fn () => [
            'first_name' => fake()->firstNameFemale(),
            'gender'     => 'female',
        ]);
    }

    /** Profile without optional fields (minimal) */
    public function minimal(): static
    {
        return $this->state(fn () => [
            'middle_name'   => null,
            'whatsapp'      => null,
            'telegram'      => null,
            'date_of_birth' => null,
            'gender'        => null,
            'avatar'        => null,
            'title'         => null,
            'address'       => null,
            'note'          => null,
        ]);
    }
}
