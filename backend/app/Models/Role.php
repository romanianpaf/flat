<?php

namespace App\Models;

use App\Support\Tenancy\TenantResolver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * The "booted" method of the model.
     *
     * IMPORTANT: NU aplicăm un global scope de tenant pe Role. Spatie rezolvă
     * rolurile după nume prin acest model, iar isSystemAdmin() -> hasRole()
     * ar reintra în scope la fiecare query => recursie infinită. Vizibilitatea
     * rolurilor pentru API e gestionată explicit în RoleSchema::indexQuery.
     */
    protected static function booted(): void
    {
        // Setează automat tenant_id la creare din contextul activ (tenantul
        // userului sau cel selectat de sysadmin prin context switch).
        static::creating(function ($role) {
            if (auth()->check() && $role->tenant_id === null) {
                $role->tenant_id = app(TenantResolver::class)->activeTenantId();
            }
        });
    }

    /**
     * Get the tenant that owns the role.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
