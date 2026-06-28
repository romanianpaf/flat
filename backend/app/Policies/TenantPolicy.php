<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TenantPolicy
{
    use HandlesAuthorization;

    // Gestionarea tenanților este o capabilitate de PLATFORMĂ (deasupra
    // tenanților), deci e rezervată operatorului de platformă (sysadmin).
    // Altfel un admin de tenant (care are toate permisiunile în tenantul lui)
    // ar putea administra toți tenanții.

    public function viewAny(User $user): Response|bool
    {
        return $user->isSystemAdmin();
    }

    public function view(User $user): Response|bool
    {
        return $user->isSystemAdmin();
    }

    public function create(User $user): Response|bool
    {
        return $user->isSystemAdmin();
    }

    public function update(User $user): Response|bool
    {
        return $user->isSystemAdmin();
    }

    public function delete(User $user): Response|bool
    {
        return $user->isSystemAdmin();
    }
}
