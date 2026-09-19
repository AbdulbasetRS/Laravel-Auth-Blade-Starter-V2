<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Tests\TestCase;

class UserCreateWizardTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([LocaleSessionRedirect::class]);

        $this->adminUser = User::factory()->create([
            'username' => 'admin_tester',
            'email' => 'admin@example.com',
            'type' => UserType::ADMIN->value,
            'status' => UserStatus::ACTIVE->value,
            'can_login' => true,
        ]);
    }

    public function test_create_user_page_renders_wizard(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertSee('id="createUserWizard"', false);
        $response->assertSee('Personal Info');
        $response->assertSee('Contact & Identity', false);
        $response->assertSee('Account & Access', false);
        $response->assertSee('Review');
    }

    public function test_check_availability_endpoint(): void
    {
        // Existing user
        $responseTaken = $this->actingAs($this->adminUser)
            ->getJson(route('admin.users.check-availability', [
                'field' => 'username',
                'value' => 'admin_tester',
            ]));

        $responseTaken->assertStatus(200)
            ->assertJson(['available' => false]);

        // Non-existing user
        $responseAvailable = $this->actingAs($this->adminUser)
            ->getJson(route('admin.users.check-availability', [
                'field' => 'username',
                'value' => 'brand_new_user',
            ]));

        $responseAvailable->assertStatus(200)
            ->assertJson(['available' => true]);
    }

    public function test_validation_errors_on_empty_post(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.users.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'username',
            'email',
            'mobile_number',
            'password',
            'status',
            'type',
            'profile.first_name',
            'profile.last_name',
        ]);
    }

    public function test_user_creation_with_profile_and_slug_generation(): void
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('avatar.png', 100, 100);

        $payload = [
            'username'              => 'john_doe',
            'email'                 => 'john.doe@example.com',
            'mobile_number'         => '+1234567890',
            'national_id'           => 'NAT-123456',
            'nationality'           => 'United States',
            'passport_number'       => 'P-987654',
            'password'              => 'SecretPassword123!',
            'password_confirmation' => 'SecretPassword123!',
            'type'                  => UserType::USER->value,
            'status'                => UserStatus::ACTIVE->value,
            'credits'               => 150,
            'can_login'             => '1',
            'status_details'        => 'Account created by admin.',
            'role_id'               => 'support_agent',
            'profile' => [
                'first_name'    => 'John',
                'middle_name'   => 'Robert',
                'last_name'     => 'Doe',
                'title'         => 'Mr.',
                'gender'        => 'male',
                'date_of_birth' => '1992-05-15',
                'whatsapp'      => '+1234567890',
                'telegram'      => '@johndoe',
                'address'       => '123 Elm Street, Cityville',
                'note'          => 'VIP user account',
            ],
            'avatar' => $avatar,
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson(route('admin.users.store'), $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('data.username', 'john_doe');

        $this->assertDatabaseHas('users', [
            'username'      => 'john_doe',
            'slug'          => 'john-doe',
            'email'         => 'john.doe@example.com',
            'mobile_number' => '+1234567890',
            'national_id'   => 'NAT-123456',
            'created_by'    => $this->adminUser->id,
        ]);

        $createdUser = User::where('username', 'john_doe')->first();
        $this->assertNotNull($createdUser);
        $this->assertNotNull($createdUser->profile);
        $this->assertEquals('John', $createdUser->profile->first_name);
        $this->assertEquals('Doe', $createdUser->profile->last_name);
        $this->assertEquals('male', $createdUser->profile->gender);
        $this->assertNotNull($createdUser->profile->avatar);

        Storage::disk('public')->assertExists($createdUser->profile->avatar);
    }

    public function test_can_login_accepts_form_data_true_false_strings(): void
    {
        $base = [
            'username'              => 'bool_user',
            'email'                 => 'bool.user@example.com',
            'mobile_number'         => '+1999888777',
            'password'              => 'SecretPassword123!',
            'password_confirmation' => 'SecretPassword123!',
            'type'                  => UserType::USER->value,
            'status'                => UserStatus::ACTIVE->value,
            'profile' => [
                'first_name' => 'Bool',
                'last_name'  => 'User',
            ],
        ];

        $trueResponse = $this->actingAs($this->adminUser)
            ->postJson(route('admin.users.store'), array_merge($base, ['can_login' => 'true']));

        $trueResponse->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'username'  => 'bool_user',
            'can_login' => 1,
        ]);

        $falseResponse = $this->actingAs($this->adminUser)
            ->postJson(route('admin.users.store'), array_merge($base, [
                'username'      => 'bool_user_off',
                'email'         => 'bool.off@example.com',
                'mobile_number' => '+1999888778',
                'can_login'     => 'false',
            ]));

        $falseResponse->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'username'  => 'bool_user_off',
            'can_login' => 0,
        ]);
    }

    public function test_slug_uniqueness_with_counter(): void
    {
        $payload1 = [
            'username'              => 'unique_user',
            'email'                 => 'user1@example.com',
            'mobile_number'         => '+1111111111',
            'password'              => 'SecretPassword123!',
            'password_confirmation' => 'SecretPassword123!',
            'type'                  => UserType::USER->value,
            'status'                => UserStatus::ACTIVE->value,
            'profile' => [
                'first_name' => 'First',
                'last_name'  => 'User',
            ],
        ];

        $payload2 = [
            'username'              => 'unique_user_2',
            'email'                 => 'user2@example.com',
            'mobile_number'         => '+2222222222',
            'password'              => 'SecretPassword123!',
            'password_confirmation' => 'SecretPassword123!',
            'type'                  => UserType::USER->value,
            'status'                => UserStatus::ACTIVE->value,
            'profile' => [
                'first_name' => 'Second',
                'last_name'  => 'User',
            ],
        ];

        $res1 = $this->actingAs($this->adminUser)->postJson(route('admin.users.store'), $payload1);
        $res1->assertStatus(201);
        $this->assertDatabaseHas('users', ['username' => 'unique_user', 'slug' => 'unique-user']);

        // Explicitly insert a collision for slug 'unique-user-2' before creating
        User::factory()->create(['slug' => 'unique-user-2']);

        $res2 = $this->actingAs($this->adminUser)->postJson(route('admin.users.store'), $payload2);
        $res2->assertStatus(201);
        $this->assertDatabaseHas('users', ['username' => 'unique_user_2', 'slug' => 'unique-user-2-1']);
    }
}
