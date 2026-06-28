<?php

namespace App\Support\CarteiImobil;

use App\Models\Apartment;
use App\Models\User;

class CarteiImobilAccess
{
    public static function isGlobalAdmin(User $user): bool
    {
        // Acces cross-tenant = operator de platformă (sysadmin).
        return $user->isSystemAdmin();
    }

    public static function isApprover(User $user): bool
    {
        // bazat pe permisiuni (nu pe rol)
        if (self::isGlobalAdmin($user)) {
            return true;
        }

        return $user->can('approve carte imobil');
    }

    public static function userBelongsToApartment(User $user, Apartment $apartment): bool
    {
        // Varianta nouă (multi-apartament): pivot
        if ($user->relationLoaded('apartments')) {
            if ($user->apartments->contains('id', $apartment->id)) {
                return true;
            }
        } else {
            if ($user->apartments()->whereKey($apartment->id)->exists()) {
                return true;
            }
        }

        // Compatibilitate cu câmpul legacy users.apartment (string)
        if ($user->tenant_id && $user->tenant_id === $apartment->tenant_id) {
            $legacy = trim((string) $user->apartment);
            if ($legacy !== '' && $legacy === (string) $apartment->number) {
                return true;
            }
        }

        return false;
    }
}

