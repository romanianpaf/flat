<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Occupant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'apartment_id',
        'first_name',
        'last_name',
        'cnp',
        'id_series',
        'id_number',
        'domicile_address',
        'role_in_unit',
        'other_role_text',
        'move_in_date',
        'move_out_date',
        'is_minor',
        'legal_guardian_name',
        'phone',
        'email',
        'notes',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'reject_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cnp' => 'encrypted',
        'id_series' => 'encrypted',
        'id_number' => 'encrypted',
        'move_in_date' => 'date:Y-m-d',
        'move_out_date' => 'date:Y-m-d',
        'is_minor' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function changeLogs(): HasMany
    {
        return $this->hasMany(OccupantChangeLog::class);
    }
}

