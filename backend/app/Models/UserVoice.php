<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserVoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'suggestion',
        'user_id',
        'tenant_id',
        'votes_up',
        'votes_down',
        'is_active',
    ];

    protected $casts = [
        'votes_up' => 'integer',
        'votes_down' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
        
        // Setează automat user_id și tenant_id la creare
        static::creating(function ($userVoice) {
            if (auth()->check()) {
                if (!$userVoice->user_id) {
                    $userVoice->user_id = auth()->id();
                }
                if (!$userVoice->tenant_id) {
                    $userVoice->tenant_id = auth()->user()->tenant_id;
                }
            }
        });
    }

    /**
     * Get the user that created the suggestion.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant that owns the suggestion.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
