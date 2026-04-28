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
        'tagline',
        'privacy_policy_url',
        'support_email',
        'message_meta_enabled',
        'show_powered_by',
        'prechat_enabled',
        'company_logo_url',
        'watermark_enabled',
        'watermark_opacity',
        'watermark_position',
        'suggested_questions',
        'welcome_buttons',
    ];

    protected $casts = [
        'suggested_questions' => 'array',
        'welcome_buttons'     => 'array',
        'message_meta_enabled' => 'boolean',
        'show_powered_by' => 'boolean',
        'prechat_enabled' => 'boolean',
        'watermark_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getDefaultConfig(): array
    {
        return [
            'welcome_message'      => 'Namaste! How can I help you today?',
            'primary_color'        => '#006d77',
            'position'             => 'bottom-right',
            'bot_name'             => 'Assistant',
            'bot_avatar_url'       => null,
            'tagline'              => null,
            'privacy_policy_url'   => null,
            'support_email'        => null,
            'message_meta_enabled' => false,
            'show_powered_by'      => true,
            'prechat_enabled'      => false,
            'company_logo_url'     => null,
            'watermark_enabled'    => false,
            'watermark_opacity'    => 0.1,
            'watermark_position'   => 'center',
            'suggested_questions'  => [],
            'welcome_buttons'      => [],
        ];
    }
}
