<?php

namespace App\Policies;

use App\Models\ServiceProviderRating;
use App\Models\User;

class ServiceProviderRatingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view service provider ratings');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceProviderRating $serviceProviderRating): bool
    {
        return $user->can('view service provider ratings') || $serviceProviderRating->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create service provider ratings');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceProviderRating $serviceProviderRating): bool
    {
        return $serviceProviderRating->user_id === $user->id || $user->can('edit service provider ratings');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceProviderRating $serviceProviderRating): bool
    {
        return $serviceProviderRating->user_id === $user->id || $user->can('delete service provider ratings');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServiceProviderRating $serviceProviderRating): bool
    {
        return $user->can('delete service provider ratings');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServiceProviderRating $serviceProviderRating): bool
    {
        return $user->can('delete service provider ratings');
    }
}
