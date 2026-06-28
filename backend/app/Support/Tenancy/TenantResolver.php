<?php

namespace App\Support\Tenancy;

/**
 * Rezolvă tenantul "activ" pentru request-ul curent.
 *
 * API-ul este stateless (token Passport), deci NU folosim session pentru
 * contextul de tenant. Contextul se derivă la momentul query-ului:
 *
 *  - user normal            -> propriul tenant_id (filtrare obligatorie)
 *  - sysadmin fără header    -> null = vedere globală (fără filtrare)
 *  - sysadmin cu X-Tenant-Id -> "intră" în acel tenant (context switch)
 *
 * Header-ul X-Tenant-Id este onorat DOAR pentru sysadmin; pentru orice alt
 * utilizator este ignorat, deci un user de tenant nu poate evada în alt tenant.
 */
class TenantResolver
{
    public const HEADER = 'X-Tenant-Id';

    /**
     * Id-ul tenantului în care lucrează request-ul curent, sau null pentru
     * acces global (sysadmin fără context switch).
     */
    public function activeTenantId(): ?int
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        if ($user->isSystemAdmin()) {
            $header = request()->header(self::HEADER);

            return ($header !== null && $header !== '') ? (int) $header : null;
        }

        return $user->tenant_id;
    }

    /**
     * True dacă request-ul nu trebuie filtrat pe tenant (sysadmin global).
     */
    public function isUnscoped(): bool
    {
        $user = auth()->user();

        return $user
            && $user->isSystemAdmin()
            && !request()->header(self::HEADER);
    }
}
