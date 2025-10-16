<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProviderRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_provider_id',
        'tenant_id',
        'user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (ServiceProviderRating $rating): void {
            if (auth()->check() && !$rating->tenant_id) {
                $rating->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
