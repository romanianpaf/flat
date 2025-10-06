<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'address',
        'fiscal_code',
        'description',
        'contact_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'contact_data' => 'array',
    ];

    /**
     * Get the roles for the tenant.
     */
    public function roles()
    {
        return $this->hasMany(\Spatie\Permission\Models\Role::class);
    }

    /**
     * Get the users for the tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the automations for the tenant.
     */
    public function automations()
    {
        return $this->hasMany(Automation::class);
    }

    /**
     * Get the polls for the tenant.
     */
    public function polls()
    {
        return $this->hasMany(Poll::class);
    }
}
