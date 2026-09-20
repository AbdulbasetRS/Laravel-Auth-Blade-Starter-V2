<?php

namespace App\Http\Requests\User;

use App\Enums\UserStatus;
use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['can_login', 'remove_avatar'] as $field) {
            if ($this->exists($field)) {
                $this->merge([$field => filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN)]);
            }
        }
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'username'       => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($userId)],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'mobile_number'  => ['required', 'string', 'max:30', Rule::unique('users', 'mobile_number')->ignore($userId)],
            'national_id'    => ['nullable', 'string', 'max:50', Rule::unique('users', 'national_id')->ignore($userId)],
            'nationality'    => ['nullable', 'string', 'max:100'],
            'passport_number'=> ['nullable', 'string', 'max:50', Rule::unique('users', 'passport_number')->ignore($userId)],
            'password'       => ['nullable', 'string', 'min:8', 'confirmed'],
            'status'         => ['required', Rule::enum(UserStatus::class)],
            'type'           => ['required', Rule::enum(UserType::class)],
            'credits'        => ['nullable', 'integer', 'min:0'],
            'can_login'      => ['nullable', 'boolean'],
            'status_details' => ['nullable', 'string', 'max:1000'],
            'role_id'        => ['nullable', 'string', 'max:100'],

            'profile.first_name'    => ['required', 'string', 'max:100'],
            'profile.middle_name'   => ['nullable', 'string', 'max:100'],
            'profile.last_name'     => ['required', 'string', 'max:100'],
            'profile.title'         => ['nullable', 'string', 'max:50'],
            'profile.gender'        => ['nullable', 'in:male,female'],
            'profile.date_of_birth' => ['nullable', 'date', 'before:today'],
            'profile.whatsapp'      => ['nullable', 'string', 'max:30'],
            'profile.telegram'      => ['nullable', 'string', 'max:100'],
            'profile.address'       => ['nullable', 'string', 'max:500'],

            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'profile.first_name' => 'first name',
            'profile.middle_name' => 'middle name',
            'profile.last_name' => 'last name',
            'profile.title' => 'title',
            'profile.gender' => 'gender',
            'profile.date_of_birth' => 'date of birth',
            'profile.whatsapp' => 'WhatsApp',
            'profile.telegram' => 'Telegram',
            'profile.address' => 'address',
        ];
    }
}
