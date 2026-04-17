<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroqUsageLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'conversation_id',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'model',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class);
    }
}
