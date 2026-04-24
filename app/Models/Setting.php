<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    private static function cacheKey(string $key): string
    {
        return 'setting:' . $key;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(static::cacheKey($key), function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(static::cacheKey($key));
    }

    public static function getAll(): array
    {
        return static::pluck('value', 'key')->toArray();
    }
}
