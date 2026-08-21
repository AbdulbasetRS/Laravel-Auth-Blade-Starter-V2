<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthProvider extends Model
{
    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_user_id',
        'provider_access_token',
        'refresh_token',
        'token_expires_at',
        'email',
        'name',
        'avatar',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at'      => 'datetime',
            'provider_access_token' => 'encrypted',
            'refresh_token'         => 'encrypted',
        ];
    }

    // ─── Relations ───────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
