<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TenantPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): Response|bool
    {
        return $user->can('view tenants');
    }

    public function view(User $user): Response|bool
    {
        return $user->can('view tenants');
    }

    public function create(User $user): Response|bool
    {
        return $user->can('create tenants');
    }

    public function update(User $user): Response|bool
    {
        return $user->can('edit tenants');
    }

    public function delete(User $user): Response|bool
    {
        return $user->can('delete tenants');
    }
}
