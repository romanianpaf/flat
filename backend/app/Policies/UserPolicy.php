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
        // Poate vedea utilizatori dacă are "view users" SAU "view members" (pentru același tenant)
        return $user->can('view users') || $user->can('view members');
    }

    public function view(User $user, User $requested): Response|bool
    {
        if ($user->is($requested)) {
            return true;
        }

        // Verifică dacă are "view users" sau "view members"
        $canViewUsers = $user->can('view users');
        $canViewMembers = $user->can('view members');

        if (!$canViewUsers && !$canViewMembers) {
            return false;
        }

        // Admin/sysadmin global poate vedea toți utilizatorii
        if ($this->isGlobalAdmin($user)) {
            return true;
        }

        // Dacă are doar "view members", poate vedea doar utilizatorii din propriul tenant
        if (!$canViewUsers && $canViewMembers) {
            return $user->tenant_id && $user->tenant_id === $requested->tenant_id;
        }

        // Utilizatorii cu "view users" și tenant pot vedea doar utilizatorii propriului tenant
        return $user->tenant_id === $requested->tenant_id;
    }

    public function create(User $user): Response|bool
    {
        // Poate crea utilizatori dacă are "create users" SAU "create members" (în același tenant)
        return $user->can('create users') || $user->can('create members');
    }

    public function update(User $user, User $requested): Response|bool
    {
        if ($user->is($requested)) {
            return true;
        }

        $canEditUsers = $user->can('edit users');
        $canEditMembers = $user->can('edit members');

        if (!$canEditUsers && !$canEditMembers) {
            return false;
        }

        // Admin/sysadmin global poate edita toți utilizatorii
        if ($this->isGlobalAdmin($user)) {
            return true;
        }

        // Dacă are doar "edit members", poate edita doar utilizatorii din propriul tenant
        if (!$canEditUsers && $canEditMembers) {
            return $user->tenant_id && $user->tenant_id === $requested->tenant_id;
        }

        // Utilizatorii cu "edit users" și tenant pot edita doar utilizatorii propriului tenant
        return $user->tenant_id === $requested->tenant_id;
    }

    public function delete(User $user, User $requested): Response|bool
    {
        // Nu poți să te ștergi pe tine însuți
        if ($user->is($requested)) {
            return false;
        }

        $canDeleteUsers = $user->can('delete users');
        $canDeleteMembers = $user->can('delete members');

        if (!$canDeleteUsers && !$canDeleteMembers) {
            return false;
        }

        // Admin/sysadmin global poate șterge toți utilizatorii
        if ($this->isGlobalAdmin($user)) {
            return true;
        }

        // Dacă are doar "delete members", poate șterge doar utilizatorii din propriul tenant
        if (!$canDeleteUsers && $canDeleteMembers) {
            return $user->tenant_id && $user->tenant_id === $requested->tenant_id;
        }

        // Utilizatorii cu "delete users" și tenant pot șterge doar utilizatorii propriului tenant
        return $user->tenant_id === $requested->tenant_id;
    }

    /**
     * Operatorul de platformă (deasupra tenanților).
     * Sursa unică de adevăr: User::isSystemAdmin().
     */
    private function isGlobalAdmin(User $user): bool
    {
        return $user->isSystemAdmin();
    }
}
