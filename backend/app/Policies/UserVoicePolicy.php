<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserVoice;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserVoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * Oricine autentificat poate vedea toate sugestiile.
     */
    public function viewAny(User $user): Response|bool
    {
        return true; // Oricine autentificat poate vedea sugestiile
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UserVoice $userVoice): Response|bool
    {
        // Admin global poate vedea toate sugestiile
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii pot vedea sugestiile propriului tenant sau fără tenant (globale)
        return $user->tenant_id === $userVoice->tenant_id || !$userVoice->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     * Oricine autentificat poate crea sugestii, inclusiv sysadmin fără tenant.
     */
    public function create(User $user): Response|bool
    {
        return true; // Oricine autentificat poate crea sugestii
    }

    /**
     * Determine whether the user can update the model.
     * Doar autorul sau adminul global poate edita.
     */
    public function update(User $user, UserVoice $userVoice): Response|bool
    {
        // Admin global poate edita toate sugestiile
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Autorul poate edita propria sugestie
        return $user->id === $userVoice->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Doar autorul sau adminul global poate șterge.
     */
    public function delete(User $user, UserVoice $userVoice): Response|bool
    {
        // Admin global poate șterge toate sugestiile
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Autorul poate șterge propria sugestie
        return $user->id === $userVoice->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserVoice $userVoice): Response|bool
    {
        return $this->delete($user, $userVoice);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserVoice $userVoice): Response|bool
    {
        return $this->delete($user, $userVoice);
    }
}
