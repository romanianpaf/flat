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

        // Lista de permisiuni CRUD pentru membri
        $permissions = [
            'create members',
            'edit members', 
            'delete members',
        ];

        // Creează permisiunile pentru ambele guard-uri
        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'api']);
        }

        // Obține permisiunile API pentru a le atribui rolurilor
        $apiPermissions = Permission::where('guard_name', 'api')
            ->whereIn('name', $permissions)
            ->get();

        // Adaugă permisiunile la rolul cex
        $cexRole = Role::where('name', 'cex')->first();
        if ($cexRole) {
            foreach ($apiPermissions as $perm) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $perm->id,
                    'role_id' => $cexRole->id,
                ]);
            }
        }

        // Adaugă și la admin și sysadmin pentru consistență
        $adminRoles = Role::whereIn('name', ['admin', 'sysadmin'])->get();
        foreach ($adminRoles as $role) {
            foreach ($apiPermissions as $perm) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $perm->id,
                    'role_id' => $role->id,
                ]);
            }
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

        $permissions = ['create members', 'edit members', 'delete members'];
        Permission::whereIn('name', $permissions)->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
