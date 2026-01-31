<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OccupantChangeLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'occupant_id',
        'user_id',
        'action',
        'changes',
        'ip',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function occupant(): BelongsTo
    {
        return $this->belongsTo(Occupant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

