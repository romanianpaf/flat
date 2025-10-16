<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceProvider extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'service_category_id',
        'service_subcategory_id',
        'created_by',
        'updated_by',
        'type',
        'first_name',
        'last_name',
        'company_name',
        'phone',
        'email',
        'photo_path',
        'service_description',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (ServiceProvider $provider): void {
            if (auth()->check()) {
                $user = auth()->user();
                $provider->created_by ??= $user->id;
                $provider->tenant_id ??= $user->tenant_id;
            }
        });

        static::updating(function (ServiceProvider $provider): void {
            if (auth()->check()) {
                $provider->updated_by = auth()->id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(ServiceSubcategory::class, 'service_subcategory_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ServiceProviderRating::class);
    }

    public function scopePublished(Builder $query, bool $published = true): Builder
    {
        return $query->where('service_providers.is_published', $published);
    }

    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('service_providers.type', $type);
    }
}
