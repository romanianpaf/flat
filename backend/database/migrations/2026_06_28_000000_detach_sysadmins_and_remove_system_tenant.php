<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Curățarea accesului de platformă după redesign-ul "system".
 *
 * Idempotentă și NON-distructivă:
 *  1. Orice user cu rolul 'sysadmin' devine tenant-less (tenant_id = NULL),
 *     pentru ca operatorul de platformă să stea în afara dimensiunii de tenant.
 *  2. Tenantul placeholder "System" (fiscal_code = 'SYSTEM') este șters DOAR
 *     dacă nu mai are nicio dată dependentă. Dacă încă are date, e lăsat intact
 *     (preferăm să nu cascadăm ștergeri).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Rolul global 'sysadmin' (tenant_id = NULL).
        $sysadminRoleId = DB::table('roles')
            ->where('name', 'sysadmin')
            ->whereNull('tenant_id')
            ->value('id');

        // 0. Garantează că operatorul de platformă cunoscut are rolul 'sysadmin'.
        //    Altfel, dacă avea doar 'admin' fără tenant, ar pierde accesul global
        //    (admin-fără-tenant nu mai înseamnă "global" după redesign).
        $platformOperatorEmail = 'alex@siteuri.pro';

        if ($sysadminRoleId) {
            $operatorId = DB::table('users')
                ->where('email', $platformOperatorEmail)
                ->value('id');

            if ($operatorId) {
                $alreadyAssigned = DB::table('model_has_roles')
                    ->where('role_id', $sysadminRoleId)
                    ->where('model_type', \App\Models\User::class)
                    ->where('model_id', $operatorId)
                    ->exists();

                if (!$alreadyAssigned) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $sysadminRoleId,
                        'model_type' => \App\Models\User::class,
                        'model_id' => $operatorId,
                    ]);
                }
            }
        }

        // 1. Detașează sysadminii de orice tenant.
        $sysadminRoleIds = DB::table('roles')->where('name', 'sysadmin')->pluck('id');

        if ($sysadminRoleIds->isNotEmpty()) {
            $sysadminUserIds = DB::table('model_has_roles')
                ->whereIn('role_id', $sysadminRoleIds)
                ->where('model_type', \App\Models\User::class)
                ->pluck('model_id');

            if ($sysadminUserIds->isNotEmpty()) {
                DB::table('users')
                    ->whereIn('id', $sysadminUserIds)
                    ->update(['tenant_id' => null]);
            }
        }

        // 2. Identifică tenantul "System".
        $systemTenant = DB::table('tenants')
            ->where('fiscal_code', 'SYSTEM')
            ->orWhere('name', 'System')
            ->first();

        if (!$systemTenant) {
            return;
        }

        // Tabele care referă tenant_id; ștergem tenantul doar dacă toate sunt goale.
        $tenantTables = [
            'users', 'roles', 'automations', 'polls', 'apartments', 'staircases',
            'user_voices', 'service_categories', 'service_subcategories',
            'service_providers', 'service_provider_ratings', 'registration_requests',
        ];

        foreach ($tenantTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'tenant_id')) {
                continue;
            }

            $hasRefs = DB::table($table)->where('tenant_id', $systemTenant->id)->exists();

            if ($hasRefs) {
                // Mai există date legate de tenantul System -> nu îl ștergem.
                return;
            }
        }

        DB::table('tenants')->where('id', $systemTenant->id)->delete();
    }

    public function down(): void
    {
        // Ireversibil în siguranță: nu recreăm tenantul System și nu reatașăm
        // sysadminii (nu știm tenantul original și nici nu ni-l dorim).
    }
};
