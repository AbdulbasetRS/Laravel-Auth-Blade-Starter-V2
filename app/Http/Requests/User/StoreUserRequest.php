<?php

namespace App\Http\Requests\User;

use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize can_login from JSON booleans, "1"/"0", and FormData "true"/"false".
     */
    protected function prepareForValidation(): void
    {
        if ($this->exists('can_login')) {
            $this->merge([
                'can_login' => filter_var($this->input('can_login'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            // ─── User account fields ─────────────────────────────────────────
            'username'        => ['required', 'string', 'max:100', Rule::unique('users', 'username')],
            'email'           => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'mobile_number'   => ['required', 'string', 'max:30', Rule::unique('users', 'mobile_number')],
            'national_id'     => ['nullable', 'string', 'max:50', Rule::unique('users', 'national_id')],
            'nationality'     => ['nullable', 'string', 'max:100'],
            'passport_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'passport_number')],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
            'status'          => ['required', Rule::enum(UserStatus::class)],
            'type'            => ['required', Rule::enum(UserType::class)],
            'credits'         => ['nullable', 'integer', 'min:0'],
            'can_login'       => ['nullable', 'boolean'],
            'status_details'  => ['nullable', 'string', 'max:1000'],
            'role_id'         => ['nullable', 'string', 'max:100'],

            // ─── Profile fields ──────────────────────────────────────────────
            'profile.first_name'   => ['required', 'string', 'max:100'],
            'profile.middle_name'  => ['nullable', 'string', 'max:100'],
            'profile.last_name'    => ['required', 'string', 'max:100'],
            'profile.title'        => ['nullable', 'string', 'max:50'],
            'profile.gender'       => ['nullable', 'in:male,female'],
            'profile.date_of_birth'=> ['nullable', 'date', 'before:today'],
            'profile.whatsapp'     => ['nullable', 'string', 'max:30'],
            'profile.telegram'     => ['nullable', 'string', 'max:100'],
            'profile.address'      => ['nullable', 'string', 'max:500'],
            'profile.note'         => ['nullable', 'string', 'max:1000'],

            // ─── Avatar ──────────────────────────────────────────────────────
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'profile.first_name'    => 'first name',
            'profile.middle_name'   => 'middle name',
            'profile.last_name'     => 'last name',
            'profile.title'         => 'title',
            'profile.gender'        => 'gender',
            'profile.date_of_birth' => 'date of birth',
            'profile.whatsapp'      => 'WhatsApp',
            'profile.telegram'      => 'Telegram',
            'profile.address'       => 'address',
            'profile.note'          => 'note',
        ];
    }
}