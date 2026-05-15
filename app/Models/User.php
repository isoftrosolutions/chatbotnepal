<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'company_name',
        'website_url',
        'plan',
        'status',
        'api_token',
        'site_id',
        'groq_api_key',
        'chatbot_enabled',
        'voice_enabled',
        'voice',
        'last_login_at',
        'plan_name',
        'monthly_amount',
        'billing_cycle',
        'next_billing_date',
        'subscription_started_at',
        'subscription_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'chatbot_enabled' => 'boolean',
        'voice_enabled' => 'boolean',
        'monthly_amount' => 'decimal:2',
        'next_billing_date' => 'date',
        'subscription_started_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->api_token)) {
                $user->api_token = Str::random(64);
            }
            if (empty($user->site_id)) {
                $user->site_id = self::generateSiteId($user->company_name ?? $user->name);
            }
        });

        static::created(function (User $user) {
            if (! $user->isClient()) {
                return;
            }

            HostedPage::firstOrCreate(
                ['client_id' => $user->id],
                [
                    'slug' => HostedPage::generateUniqueSlug($user->company_name ?? $user->name),
                    'status' => 'active',
                    'public_config' => HostedPage::defaultPublicConfig($user),
                    'behavior_config' => HostedPage::defaultBehaviorConfig(),
                ]
            );
        });
    }

    public static function generateSiteId(?string $name): string
    {
        $base = Str::slug($name ?? 'client', '');
        $unique = Str::lower(Str::random(6));

        return $base ? substr($base, 0, 20).'-'.$unique : 'client-'.$unique;
    }

    public function knowledgeBases(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function clientLinks(): HasMany
    {
        return $this->hasMany(ClientLink::class);
    }

    public function tokenUsageLogs(): HasMany
    {
        return $this->hasMany(TokenUsageLog::class);
    }

    public function widgetConfig(): HasOne
    {
        return $this->hasOne(WidgetConfig::class);
    }

    public function hostedPages(): HasMany
    {
        return $this->hasMany(HostedPage::class, 'client_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
