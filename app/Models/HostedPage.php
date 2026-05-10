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
            'tagline' => 'AI Assistant',
            'welcome_message' => 'Hello, how can we help you today?',
            'logo_url' => null,
            'cover_image_url' => null,
            'quick_actions' => [
                ['label' => 'Check Room Availability', 'icon' => 'calendar'],
                ['label' => 'View Room Prices', 'icon' => 'currency'],
                ['label' => 'Hotel Location', 'icon' => 'location'],
            ],
            'branding' => [
                'primary' => '#3B1FA8',
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

    public function getTitleAttribute(): string
    {
        return $this->public_config['title'] ?? $this->client?->company_name ?? $this->client?->name ?? 'AI Assistant';
    }

    public function getTaglineAttribute(): string
    {
        return $this->public_config['tagline'] ?? 'AI Assistant';
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->public_config['logo_url'] ?? null;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->public_config['cover_image_url'] ?? null;
    }

    public function getWelcomeMessageAttribute(): string
    {
        return $this->public_config['welcome_message'] ?? 'Hello, how can we help you today?';
    }

    public function getBrandColorAttribute(): string
    {
        return $this->public_config['branding']['primary'] ?? '#3B1FA8';
    }

    public function getQuickActionsAttribute(): array
    {
        return $this->public_config['quick_actions'] ?? [
            ['label' => 'Check Room Availability', 'icon' => 'calendar'],
            ['label' => 'View Room Prices', 'icon' => 'currency'],
            ['label' => 'Hotel Location', 'icon' => 'location'],
        ];
    }
}
