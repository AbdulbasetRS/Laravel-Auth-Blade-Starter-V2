<?php

namespace App\Helpers;

class StoragePath
{
    /**
     * storage/app/public/users/{user_id}/avatars
     */
    public static function userAvatar(int $userId): string
    {
        return "users/{$userId}/avatars";
    }

    /**
     * storage/app/public/users/{user_id}/covers
     */
    public static function userCover(int $userId): string
    {
        return "users/{$userId}/covers";
    }
}
