<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): Response|bool
    {
        return $user->can('view users');
    }

    public function view(User $user, User $requested): Response|bool
    {
        if ($user->is($requested)) {
            return true;
        }

        if (!$user->can('view users')) {
            return false;
        }

        // Admin/sysadmin global poate vedea toți utilizatorii
        if ($this->isGlobalAdmin($user)) {
            return true;
        }

        // Utilizatorii cu tenant pot vedea doar utilizatorii propriului tenant
        return $user->tenant_id === $requested->tenant_id;
    }

    public function create(User $user): Response|bool
    {
        return $user->can('create users');
    }

    public function update(User $user, User $requested): Response|bool
    {
        if ($user->is($requested)) {
            return true;
        }

        if (!$user->can('edit users')) {
            return false;
        }

        // Admin/sysadmin global poate edita toți utilizatorii
        if ($this->isGlobalAdmin($user)) {
            return true;
        }

        // Utilizatorii cu tenant pot edita doar utilizatorii propriului tenant
        return $user->tenant_id === $requested->tenant_id;
    }

    public function delete(User $user, User $requested): Response|bool
    {
        // Nu poți să te ștergi pe tine însuți
        if ($user->is($requested)) {
            return false;
        }

        if (!$user->can('delete users')) {
            return false;
        }

        // Admin/sysadmin global poate șterge toți utilizatorii
        if ($this->isGlobalAdmin($user)) {
            return true;
        }

        // Utilizatorii cu tenant pot șterge doar utilizatorii propriului tenant
        return $user->tenant_id === $requested->tenant_id;
    }

    /**
     * Check if user is a global admin (admin or sysadmin without tenant)
     */
    private function isGlobalAdmin(User $user): bool
    {
        // Sysadmin are acces global indiferent de tenant
        if ($user->hasRole('sysadmin')) {
            return true;
        }

        // Admin fără tenant are acces global
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        return false;
    }
}
