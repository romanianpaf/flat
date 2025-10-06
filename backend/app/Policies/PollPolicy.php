<?php

namespace App\Policies;

use App\Models\Poll;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PollPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): Response|bool
    {
        return $user->can('view polls');
    }

    public function view(User $user, Poll $poll): Response|bool
    {
        if (!$user->can('view polls')) {
            return false;
        }

        // Admin global poate vedea toate sondajele
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii cu tenant pot vedea doar sondajele propriului tenant
        return $user->tenant_id === $poll->tenant_id;
    }

    public function create(User $user): Response|bool
    {
        return $user->can('create polls');
    }

    public function update(User $user, Poll $poll): Response|bool
    {
        if (!$user->can('edit polls')) {
            return false;
        }

        // Admin global poate edita toate sondajele
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii cu tenant pot edita doar sondajele propriului tenant
        return $user->tenant_id === $poll->tenant_id;
    }

    public function delete(User $user, Poll $poll): Response|bool
    {
        if (!$user->can('delete polls')) {
            return false;
        }

        // Admin global poate șterge toate sondajele
        if (!$user->tenant_id && $user->hasRole('admin')) {
            return true;
        }

        // Utilizatorii cu tenant pot șterge doar sondajele propriului tenant
        return $user->tenant_id === $poll->tenant_id;
    }

    public function restore(User $user, Poll $poll): Response|bool
    {
        return $this->delete($user, $poll);
    }

    public function forceDelete(User $user, Poll $poll): Response|bool
    {
        return $this->delete($user, $poll);
    }
}
