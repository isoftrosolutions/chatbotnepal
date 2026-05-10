<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ChatSession extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'client_id',
        'hosted_page_id',
        'conversation_id',
        'channel',
        'channel_ref',
        'visitor_fingerprint',
        'message_count',
        'lead_status',
        'meta',
        'last_message_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'last_message_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ChatSession $session) {
            if (! $session->id) {
                $session->id = (string) Str::uuid();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function hostedPage(): BelongsTo
    {
        return $this->belongsTo(HostedPage::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class);
    }
}
