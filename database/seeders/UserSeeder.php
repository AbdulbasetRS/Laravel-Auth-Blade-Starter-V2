<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        $admin = User::create([
            'id' => 1,
            'username' => 'abdulbaset_rs',
            'slug' => Str::slug('abdulbaset_rs'),
            'email' => 'abdulbaset_rs@digitalatum.com',
            'mobile_number' => '01097579845',
            'password' => Hash::make('123456789'),
            'status' => 'active',
            'type' => 'admin',
            'can_login' => true,
            'email_verified_at' => now(),
        ]);
        $admin->profile()->create([
            'first_name' => 'Abdulbaset',
            'middle_name' => 'Reda',
            'last_name' => 'Sayed',
            'gender' => 'male',
            'title' => 'مبرمج Laravel',
            'date_of_birth' => '1995-01-01',
            'whatsapp' => '01097579845',
            'telegram' => '01097579845',
            'address' => 'الجيزة - إمبابة',
            'note' => 'صاحب المشروع',
            'avatar' => 'https://ui-avatars.com/api/?name=Abdulbaset+Sayed&size=512&background=random',
        ]);
        $admin->authProviders()->create([
            'provider_name' => 'google',
            'provider_user_id' => '1234567890',
            'email' => 'abdulbaset_rs@digitalatum.com',
            'name' => 'Abdulbaset R. Sayed',
            'avatar' => 'https://ui-avatars.com/api/?name=Abdulbaset+Sayed&size=512&background=random',
        ]);
        $admin->settings()->create([
            'enable_two_factor' => false,
            'timezone' => 'Africa/Cairo',
            'date_format' => 'Y-m-d',
            'time_format' => '24h',
            'notifications_email' => true,
            'notifications_sound' => true,
        ]);


        // ─── 1. Super Admin (ثابت دائماً) ─────────────────────────────────────
        User::factory()->admin()->create([
            'username'      => 'admin',
            'slug'          => 'admin',
            'email'         => 'admin@test.com',
            'mobile_number' => '+201000000001',
            'password'      => Hash::make('123456789'),
            'type'          => UserType::ADMIN->value,
            'status'        => UserStatus::ACTIVE->value,
        ]);

        // ─── 2. IT User ───────────────────────────────────────────────────────
        User::factory()->active()->create([
            'username'      => 'it_user',
            'slug'          => 'it-user',
            'email'         => 'it@test.com',
            'mobile_number' => '+201000000002',
            'password'      => Hash::make('123456789'),
            'type'          => UserType::IT->value,
        ]);

        // ─── 3. Tester ────────────────────────────────────────────────────────
        User::factory()->active()->create([
            'username'      => 'tester',
            'slug'          => 'tester',
            'email'         => 'tester@test.com',
            'mobile_number' => '+201000000003',
            'password'      => Hash::make('123456789'),
            'type'          => UserType::TESTER->value,
        ]);

        // ─── 4. Random users by status ────────────────────────────────────────
        User::factory(40)->active()->create();
        User::factory(20)->pending()->unverified()->create();
        User::factory(15)->suspended()->create();
        User::factory(10)->banned()->create();
        User::factory(15)->create();   // mixed random statuses
    }
}
