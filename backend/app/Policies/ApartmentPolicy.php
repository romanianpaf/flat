<?php

namespace App\Policies;

use App\Models\Apartment;
use App\Models\User;
use App\Support\CarteiImobil\CarteiImobilAccess;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ApartmentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Apartment $apartment): Response|bool
    {
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        // trebuie să aibă permisiunea de view
        if (!$user->can('view carte imobil')) {
            return false;
        }

        if ($user->tenant_id && $user->tenant_id === $apartment->tenant_id) {
            // admin/cex: vede tot tenant-ul
            if ($user->can('approve carte imobil') || $user->can('configure apartments')) {
                return true;
            }

            // member/locatar: doar apartamentul lui
            return CarteiImobilAccess::userBelongsToApartment($user, $apartment);
        }

        return false;
    }

    public function approve(User $user, Apartment $apartment): Response|bool
    {
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        return $user->tenant_id
            && $user->tenant_id === $apartment->tenant_id
            && $user->can('approve carte imobil');
    }
}

