<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'welcome_message',
        'primary_color',
        'position',
        'bot_name',
        'bot_avatar_url',
        'show_powered_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getDefaultConfig(): array
    {
        return [
            'welcome_message' => 'Namaste! How can I help you today?',
            'primary_color' => '#4F46E5',
            'position' => 'bottom-right',
            'bot_name' => 'Assistant',
            'bot_avatar_url' => null,
            'show_powered_by' => true,
        ];
    }
}
