<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Creează permisiunea "view members" pentru ambele guard-uri
        Permission::firstOrCreate(['name' => 'view members', 'guard_name' => 'web']);
        $permissionApi = Permission::firstOrCreate(['name' => 'view members', 'guard_name' => 'api']);

        // Adaugă permisiunea la rolul cex (folosind direct query pentru a evita probleme cu guard)
        $cexRole = Role::where('name', 'cex')->first();
        if ($cexRole) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionApi->id,
                'role_id' => $cexRole->id,
            ]);
        }

        // Adaugă și la admin și sysadmin pentru consistență
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionApi->id,
                'role_id' => $adminRole->id,
            ]);
        }

        $sysadminRole = Role::where('name', 'sysadmin')->first();
        if ($sysadminRole) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permissionApi->id,
                'role_id' => $sysadminRole->id,
            ]);
        }

        // Reset cache after changes
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Șterge permisiunile
        Permission::where('name', 'view members')->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
