<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
