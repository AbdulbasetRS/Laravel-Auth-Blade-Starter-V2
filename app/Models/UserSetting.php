<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $table = 'user_settings';

    protected $fillable = [
        'user_id',
        'enable_two_factor',
        'google2fa_secret',
        'timezone',
        'date_format',
        'time_format',
        'notifications_email',
        'notifications_sound',
    ];

    protected function casts(): array
    {
        return [
            'enable_two_factor'    => 'boolean',
            'notifications_email'  => 'boolean',
            'notifications_sound'  => 'boolean',
            'google2fa_secret'     => 'encrypted',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
