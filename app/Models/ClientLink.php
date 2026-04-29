<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ClientLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'link',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (ClientLink $link) {
            if (empty($link->slug)) {
                $link->slug = Str::slug($link->name);
            }
            if (is_null($link->sort_order)) {
                $link->sort_order = 0;
            }
        });

        static::updating(function (ClientLink $link) {
            if ($link->isDirty('name') && empty($link->getOriginal('slug'))) {
                $link->slug = Str::slug($link->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}