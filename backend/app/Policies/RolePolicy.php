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
        if ($user->roles->contains($role)) {
            return true;
        }

        if (!$user->can('view roles')) {
            return false;
        }

        // Admin global poate vedea toate rolurile
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii cu tenant pot vedea doar rolurile propriului tenant
        return $user->tenant_id === $role->tenant_id;
    }

    public function create(User $user): Response|bool
    {
        return $user->can('create roles');
    }

    public function update(User $user, Role $role): Response|bool
    {
        if (!$user->can('edit roles')) {
            return false;
        }

        // Admin global poate edita toate rolurile
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii cu tenant pot edita doar rolurile propriului tenant
        return $user->tenant_id === $role->tenant_id;
    }

    public function delete(User $user, Role $role): Response|bool
    {
        if (!$user->can('delete roles')) {
            return false;
        }

        // Admin global poate șterge toate rolurile
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii cu tenant pot șterge doar rolurile propriului tenant
        return $user->tenant_id === $role->tenant_id;
    }
}
