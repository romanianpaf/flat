<?php

namespace App\Policies;

use App\Models\PollOption;
use App\Models\User;

class PollOptionPolicy
{
    /**
     * Determine whether the user can view the poll option.
     */
    public function view(User $user, PollOption $pollOption): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create poll options.
     */
    public function create(User $user): bool
    {
        return $user->can('create polls');
    }

    /**
     * Determine whether the user can update the poll option.
     */
    public function update(User $user, PollOption $pollOption): bool
    {
        return $user->can('edit polls');
    }

    /**
     * Determine whether the user can delete the poll option.
     */
    public function delete(User $user, PollOption $pollOption): bool
    {
        return $user->can('delete polls');
    }
}
