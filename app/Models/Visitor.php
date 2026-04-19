<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    protected $fillable = [
        'user_id',
        'visitor_uuid',
        'name',
        'email',
        'phone',
        'ip_address',
        'user_agent',
        'last_page_url',
        'first_seen_at',
        'last_seen_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class, 'visitor_id', 'visitor_uuid')
            ->where('user_id', $this->user_id);
    }
}
