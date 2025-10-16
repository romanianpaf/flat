<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (ServiceCategory $category): void {
            if (auth()->check() && !$category->tenant_id) {
                $category->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(ServiceSubcategory::class);
    }

    public function providers(): HasMany
    {
        return $this->hasMany(ServiceProvider::class);
    }

    public function scopeName(Builder $query, string $value): Builder
    {
        return $query->where('service_categories.name', 'LIKE', "%{$value}%", 'or');
    }

    public function scopeDescription(Builder $query, string $value): Builder
    {
        return $query->where('service_categories.description', 'LIKE', "%{$value}%", 'or');
    }
}
