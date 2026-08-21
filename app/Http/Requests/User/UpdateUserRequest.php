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
            'password'       => ['nullable', 'string', 'min:8'],
            'status'         => ['required', Rule::enum(UserStatus::class)],
            'type'           => ['required', Rule::enum(UserType::class)],
            'credits'        => ['nullable', 'integer', 'min:0'],
            'can_login'      => ['nullable', 'boolean'],
            'status_details' => ['nullable', 'string', 'max:1000'],
            'role_id'        => ['nullable', 'string', 'max:100'],
        ];
    }
}