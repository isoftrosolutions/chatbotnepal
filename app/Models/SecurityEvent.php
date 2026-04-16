<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $event, ?int $userId, string $ip, string $userAgent): void
    {
        static::create([
            'user_id'    => $userId,
            'event'      => $event,
            'ip'         => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}
