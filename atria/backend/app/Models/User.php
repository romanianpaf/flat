<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role',
        'status', // 'active', 'inactive', 'suspended'
        'last_login_at',
        'settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'settings' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function poolAccessLogs()
    {
        return $this->hasMany(PoolAccessLog::class);
    }

    public function isSysAdmin()
    {
        return $this->role === 'sysadmin';
    }

    public function isAdmin()
    {
        return $this->role === 'admin' || $this->role === 'sysadmin';
    }

    public function isTenantAdmin()
    {
        return $this->role === 'tenantadmin' || $this->role === 'admin' || $this->role === 'sysadmin';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function canAccessTenant($tenantId)
    {
        // Sysadmin poate accesa orice tenant
        if ($this->isSysAdmin()) {
            return true;
        }

        // Alți utilizatori pot accesa doar tenant-ul lor
        return $this->tenant_id === $tenantId;
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }

    public function updateLastLogin()
    {
        $this->update(['last_login_at' => now()]);
    }
}
