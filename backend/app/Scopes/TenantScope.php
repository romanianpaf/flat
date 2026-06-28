<?php

namespace App\Scopes;

use App\Support\Tenancy\TenantResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtrare strictă pe tenant pentru date deținute integral de un tenant
 * (Automation, Poll, UserVoice, Service*).
 *
 * Contextul vine din TenantResolver (stateless), nu din session:
 *  - sysadmin global         -> fără filtrare
 *  - user / sysadmin în context -> tenant_id = activeTenantId()
 *  - user fără tenant         -> niciun rând (safety)
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            return;
        }

        $resolver = app(TenantResolver::class);

        // Sysadmin fără context switch vede tot.
        if ($resolver->isUnscoped()) {
            return;
        }

        $tenantId = $resolver->activeTenantId();
        $column = $model->getTable() . '.tenant_id';

        // User autentificat fără tenant și fără drept global: nu vede nimic.
        if ($tenantId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($column, $tenantId);
    }
}
