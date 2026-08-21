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

    public function rules(): array
    {
        return [
            'username'       => ['required', 'string', 'max:100', Rule::unique('users', 'username')],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'mobile_number'  => ['required', 'string', 'max:30', Rule::unique('users', 'mobile_number')],
            'national_id'    => ['nullable', 'string', 'max:50', Rule::unique('users', 'national_id')],
            'nationality'    => ['nullable', 'string', 'max:100'],
            'passport_number'=> ['nullable', 'string', 'max:50', Rule::unique('users', 'passport_number')],
            'password'       => ['required', 'string', 'min:8'],
            'status'         => ['required', Rule::enum(UserStatus::class)],
            'type'           => ['required', Rule::enum(UserType::class)],
            'credits'        => ['nullable', 'integer', 'min:0'],
            'can_login'      => ['nullable', 'boolean'],
            'status_details' => ['nullable', 'string', 'max:1000'],
            'role_id'        => ['nullable', 'string', 'max:100'],
        ];
    }
}