<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'tokens_input',
        'tokens_output',
        'total_tokens',
        'api_calls',
        'estimated_cost_npr',
    ];

    protected $casts = [
        'date' => 'date',
        'tokens_input' => 'integer',
        'tokens_output' => 'integer',
        'total_tokens' => 'integer',
        'api_calls' => 'integer',
        'estimated_cost_npr' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addUsage(int $inputTokens, int $outputTokens, float $cost): void
    {
        $this->update([
            'tokens_input' => $this->tokens_input + $inputTokens,
            'tokens_output' => $this->tokens_output + $outputTokens,
            'total_tokens' => $this->total_tokens + $inputTokens + $outputTokens,
            'api_calls' => $this->api_calls + 1,
            'estimated_cost_npr' => $this->estimated_cost_npr + $cost,
        ]);
    }

    public static function getOrCreateForToday(int $userId): static
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'date' => now()->toDateString()],
            [
                'tokens_input' => 0,
                'tokens_output' => 0,
                'total_tokens' => 0,
                'api_calls' => 0,
                'estimated_cost_npr' => 0,
            ]
        );
    }
}
