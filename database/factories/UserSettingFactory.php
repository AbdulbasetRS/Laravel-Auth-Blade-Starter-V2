<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserSetting>
 */
class UserSettingFactory extends Factory
{
    private const DATE_FORMATS = ['Y-m-d', 'd-m-Y', 'm/d/Y', 'd/m/Y', 'M d, Y'];
    private const TIME_FORMATS = ['24h', '12h'];
    private const TIMEZONES = [
        'Africa/Cairo', 'Asia/Riyadh', 'Asia/Dubai', 'Asia/Kuwait',
        'Europe/London', 'America/New_York', 'America/Los_Angeles',
        'Asia/Beirut', 'Africa/Casablanca',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'              => User::factory(),
            'enable_two_factor'    => fake()->boolean(10),   // 10% have 2FA enabled
            'google2fa_secret'     => null,
            'timezone'             => fake()->randomElement(self::TIMEZONES),
            'date_format'          => fake()->randomElement(self::DATE_FORMATS),
            'time_format'          => fake()->randomElement(self::TIME_FORMATS),
            'notifications_email'  => fake()->boolean(60),   // 60% have email notifications
            'notifications_sound'  => fake()->boolean(40),   // 40% have sound notifications
        ];
    }

    // ─── Named States ─────────────────────────────────────────────────────────

    /** 2FA fully enabled with a secret */
    public function withTwoFactor(): static
    {
        return $this->state(fn () => [
            'enable_two_factor' => true,
            'google2fa_secret'  => \Illuminate\Support\Str::random(32),
        ]);
    }

    /** All notifications disabled */
    public function silent(): static
    {
        return $this->state(fn () => [
            'notifications_email' => false,
            'notifications_sound' => false,
        ]);
    }

    /** All notifications enabled */
    public function allNotifications(): static
    {
        return $this->state(fn () => [
            'notifications_email' => true,
            'notifications_sound' => true,
        ]);
    }

    /** Cairo / Egypt defaults */
    public function egyptian(): static
    {
        return $this->state(fn () => [
            'timezone'    => 'Africa/Cairo',
            'date_format' => 'd-m-Y',
            'time_format' => '24h',
        ]);
    }
}
