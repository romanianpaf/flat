<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RegistrationRequestPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view registration requests',
            'approve registration requests',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name);
        }

        // Roles that can manage registration requests
        $byRole = [
            'admin' => $permissions,
            'cex' => $permissions,
            'sysadmin' => $permissions,
        ];

        foreach ($byRole as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) {
                continue;
            }

            $role->givePermissionTo($perms);
        }
    }
}
