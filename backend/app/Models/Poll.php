<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'is_active',
        'allow_multiple_votes',
        'start_date',
        'end_date',
        'tenant_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_multiple_votes' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
        
        // Setează automat tenant_id la creare
        static::creating(function ($poll) {
            if (auth()->check() && !$poll->tenant_id) {
                $poll->tenant_id = app(\App\Support\Tenancy\TenantResolver::class)->activeTenantId();
            }
        });
    }

    /**
     * Get the tenant that owns the poll.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the options for the poll.
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('order');
    }
}
