<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): Response|bool
    {
        return $user->can('view roles');
    }

    public function view(User $user, Role $role): Response|bool
    {
        // Verifică permisiunea de bază
        if (!$user->can('view roles')) {
            return false;
        }

        // Utilizatorii pot vedea întotdeauna propriul rol
        if ($user->roles->contains($role)) {
            return true;
        }

        // Sysadmin (tenant_id = 1 pentru "System") poate vedea toate rolurile
        if ($user->tenant_id === 1 && $user->can('edit roles') && $user->can('delete roles')) {
            return true;
        }

        // Ceilalți utilizatori pot vedea doar rolurile propriului tenant
        return $user->tenant_id === $role->tenant_id;
    }

    public function create(User $user): Response|bool
    {
        return $user->can('create roles');
    }

    public function update(User $user, Role $role): Response|bool
    {
        // Verifică permisiunea de bază
        if (!$user->can('edit roles')) {
            return false;
        }

        // Sysadmin (tenant_id = 1 pentru "System") poate edita toate rolurile
        if ($user->tenant_id === 1 && $user->can('delete roles')) {
            return true;
        }

        // Ceilalți utilizatori pot edita doar rolurile propriului tenant
        return $user->tenant_id === $role->tenant_id;
    }

    public function delete(User $user, Role $role): Response|bool
    {
        // Verifică permisiunea de bază
        if (!$user->can('delete roles')) {
            return false;
        }

        // Sysadmin (tenant_id = 1 pentru "System") poate șterge toate rolurile
        if ($user->tenant_id === 1) {
            return true;
        }

        // Ceilalți utilizatori pot șterge doar rolurile propriului tenant
        return $user->tenant_id === $role->tenant_id;
    }
}
