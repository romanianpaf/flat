<?php

namespace App\Policies;

use App\Models\Automation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AutomationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->can('view automations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Automation $automation): Response|bool
    {
        if (!$user->can('view automations')) {
            return false;
        }

        // Admin global poate vedea toate automatizările
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii cu tenant pot vedea doar automatizările propriului tenant
        return $user->tenant_id === $automation->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response|bool
    {
        return $user->can('create automations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Automation $automation): Response|bool
    {
        if (!$user->can('edit automations')) {
            return false;
        }

        // Admin global poate edita toate automatizările
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii cu tenant pot edita doar automatizările propriului tenant
        return $user->tenant_id === $automation->tenant_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Automation $automation): Response|bool
    {
        if (!$user->can('delete automations')) {
            return false;
        }

        // Admin global poate șterge toate automatizările
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii cu tenant pot șterge doar automatizările propriului tenant
        return $user->tenant_id === $automation->tenant_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Automation $automation): Response|bool
    {
        return $this->delete($user, $automation);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Automation $automation): Response|bool
    {
        return $this->delete($user, $automation);
    }
}
