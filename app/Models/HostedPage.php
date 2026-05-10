<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class HostedPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'slug',
        'status',
        'public_config',
        'behavior_config',
        'custom_domain',
        'domain_verification_token',
        'domain_verified_at',
    ];

    protected $casts = [
        'public_config' => 'array',
        'behavior_config' => 'array',
        'domain_verified_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'client-chat';
        }

        $slug = $base;
        $suffix = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public static function defaultPublicConfig(User $client): array
    {
        $title = $client->company_name ?: $client->name;

        return [
            'title' => $title,
            'welcome_message' => 'Hello, how can we help you today?',
            'logo_url' => null,
            'branding' => [
                'primary' => '#0f766e',
                'bg' => '#f8fafc',
                'font' => 'system-ui, sans-serif',
            ],
        ];
    }

    public static function defaultBehaviorConfig(): array
    {
        return [
            'lead_capture' => ['enabled' => true, 'trigger_depth' => 6],
            'rate_limit' => ['per_hour' => 30],
        ];
    }
}
