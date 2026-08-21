<?php

namespace App\Enums;

enum UserType: string
{
    case USER = 'user';
    case ADMIN = 'admin';
    case IT = 'it';
    case TESTER = 'tester';
    case EMPLOYEE = 'employee';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::USER => __('User'),
            self::ADMIN => __('Admin'),
            self::IT => __('IT'),
            self::TESTER => __('Tester'),
            self::EMPLOYEE => __('Employee'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::USER => 'success',
            self::ADMIN => 'danger',
            self::IT => 'warning',
            self::TESTER => 'info',
            self::EMPLOYEE => 'primary',
        };
    }
}
