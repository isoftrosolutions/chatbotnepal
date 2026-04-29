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
        'last_login_at',
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

    public function tokenUsageLogs(): HasMany
    {
        return $this->hasMany(TokenUsageLog::class);
    }

    public function widgetConfig(): HasOne
    {
        return $this->hasOne(WidgetConfig::class);
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
