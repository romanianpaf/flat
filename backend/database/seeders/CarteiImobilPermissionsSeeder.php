<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CarteiImobilPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view carte imobil',
            'create carte imobil',
            'edit carte imobil',
            'delete carte imobil',
            'approve carte imobil',
            'export carte imobil',
            'configure apartments',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name);
        }

        // Assignări sigure (fără truncate)
        $byRole = [
            'admin' => $permissions,
            'cex' => $permissions,
            'administrație' => array_values(array_diff($permissions, ['approve carte imobil'])),
            'sysadmin' => $permissions,
            'member' => [
                'view carte imobil',
                'create carte imobil',
                'edit carte imobil',
                'delete carte imobil',
                'export carte imobil',
            ],
            'locatar' => [
                'view carte imobil',
                'create carte imobil',
                'edit carte imobil',
                'delete carte imobil',
                'export carte imobil',
            ],
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

