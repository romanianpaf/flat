<?php

namespace App\Policies;

use App\Models\Apartment;
use App\Models\Occupant;
use App\Models\User;
use App\Support\CarteiImobil\CarteiImobilAccess;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class OccupantPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, Apartment $apartment): Response|bool
    {
        if (!$user->can('view carte imobil') && !CarteiImobilAccess::isGlobalAdmin($user)) {
            return false;
        }

        return app(ApartmentPolicy::class)->view($user, $apartment);
    }

    public function view(User $user, Occupant $occupant): Response|bool
    {
        return app(ApartmentPolicy::class)->view($user, $occupant->apartment);
    }

    public function create(User $user, Apartment $apartment): Response|bool
    {
        if (!$user->can('create carte imobil') && !CarteiImobilAccess::isGlobalAdmin($user)) {
            return false;
        }

        return app(ApartmentPolicy::class)->view($user, $apartment);
    }

    public function update(User $user, Occupant $occupant): Response|bool
    {
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        if (!$user->can('edit carte imobil')) {
            return false;
        }

        // Manager de tenant (admin/cex/administrație): poate edita orice locatar
        // din tenantul lui, indiferent de status.
        if (CarteiImobilAccess::isTenantManager($user, $occupant->apartment)) {
            return true;
        }

        // Locatar: doar pe apartamentul lui și doar în draft/rejected.
        if (!CarteiImobilAccess::userBelongsToApartment($user, $occupant->apartment)) {
            return false;
        }

        return in_array($occupant->status, ['draft', 'rejected'], true);
    }

    public function delete(User $user, Occupant $occupant): Response|bool
    {
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        if (!$user->can('delete carte imobil')) {
            return false;
        }

        // Manager de tenant: poate șterge orice locatar din tenantul lui.
        if (CarteiImobilAccess::isTenantManager($user, $occupant->apartment)) {
            return true;
        }

        // Locatar: doar pe apartamentul lui și doar în draft/rejected.
        if (!CarteiImobilAccess::userBelongsToApartment($user, $occupant->apartment)) {
            return false;
        }

        return in_array($occupant->status, ['draft', 'rejected'], true);
    }

    public function submit(User $user, Apartment $apartment): Response|bool
    {
        // Locatar doar pe apartamentul lui
        if (!$user->can('edit carte imobil') && !$user->can('create carte imobil')) {
            return false;
        }

        return CarteiImobilAccess::userBelongsToApartment($user, $apartment);
    }

    public function approve(User $user, Apartment $apartment): Response|bool
    {
        return app(ApartmentPolicy::class)->approve($user, $apartment);
    }

    public function export(User $user, Apartment $apartment): Response|bool
    {
        if (CarteiImobilAccess::isGlobalAdmin($user)) {
            return true;
        }

        // Admin/comitet: poate exporta pentru tenant
        if ($user->tenant_id && $user->tenant_id === $apartment->tenant_id && $user->can('export carte imobil')) {
            return true;
        }

        // Locatar: doar dacă cererea e aprobată (verificare în controller pe status agregat)
        return $user->can('export carte imobil') && CarteiImobilAccess::userBelongsToApartment($user, $apartment);
    }
}

