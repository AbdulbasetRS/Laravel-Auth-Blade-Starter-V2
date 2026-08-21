<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case BANNED = 'banned';
    case PENDING = 'pending';
    case DELETED = 'deleted';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive'),
            self::SUSPENDED => __('Suspended'),
            self::BANNED => __('Banned'),
            self::PENDING => __('Pending'),
            self::DELETED => __('Deleted'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'secondary',
            self::SUSPENDED => 'warning',
            self::BANNED => 'danger',
            self::PENDING => 'info',
            self::DELETED => 'danger',
        };
    }

    public static function toSelectOptions(): array
    {
        return collect(self::cases())->map(function ($status) {
            return [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ];
        })->toArray();
    }
}