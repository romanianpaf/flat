<?php

namespace App\Policies;

use App\Models\ServiceSubcategory;
use App\Models\User;

class ServiceSubcategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view service subcategories');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ServiceSubcategory $serviceSubcategory): bool
    {
        return $user->can('view service subcategories');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create service subcategories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ServiceSubcategory $serviceSubcategory): bool
    {
        return $user->can('edit service subcategories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ServiceSubcategory $serviceSubcategory): bool
    {
        return $user->can('delete service subcategories');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ServiceSubcategory $serviceSubcategory): bool
    {
        return $user->can('delete service subcategories');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ServiceSubcategory $serviceSubcategory): bool
    {
        return $user->can('delete service subcategories');
    }
}
