<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileNameGenerator
{
    /**
     * Generate a unique file name.
     *
     * Pattern: {model}_{type}_{id}_{date}_{time}_{unique}.{ext}
     * Example: user_avatar_15_20260616_143522347_abcd1234.jpg
     *
     * @param  string  $model     e.g. 'user', 'property'
     * @param  string  $type      e.g. 'avatar', 'cover', 'image', 'video', 'document'
     * @param  int     $modelId   the model primary key
     * @param  string  $extension file extension without dot  e.g. 'jpg'
     */
    public static function make(string $model, string $type, int $modelId, string $extension = ''): string
    {
        $date   = now()->format('Ymd');           // 20260616
        $time   = now()->format('Hisv');          // 143522347  (H=hour i=min s=sec v=ms)
        $unique = Str::lower(Str::random(8));     // abcd1234

        $name = implode('_', [
            strtolower($model),
            strtolower($type),
            $modelId,
            $date,
            $time,
            $unique,
        ]);

        if ($extension) {
            $name .= '.'.ltrim(strtolower($extension), '.');
        }

        return $name;
    }

    /**
     * Generate from an uploaded file (extension auto-detected).
     */
    public static function makeFromFile(
        string $model,
        string $type,
        int $modelId,
        UploadedFile $file
    ): string {
        return self::make($model, $type, $modelId, $file->getClientOriginalExtension());
    }
}
