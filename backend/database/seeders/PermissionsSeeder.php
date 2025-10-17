<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('roles')->truncate();
        Schema::enableForeignKeyConstraints();

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // User permissions
            'view users', 'create users', 'edit users', 'delete users',

            // Role permissions
            'view roles', 'create roles', 'edit roles', 'delete roles',

            // Permissions permissions
            'view permissions',

            // Category permissions
            'view categories', 'create categories', 'edit categories', 'delete categories',

            // Tag permissions
            'view tags', 'create tags', 'edit tags', 'delete tags',

            // Item permissions
            'view items', 'create items', 'edit items', 'delete items',

            // Tenant permissions
            'view tenants', 'create tenants', 'edit tenants', 'delete tenants',

            // Automation permissions
            'view automations', 'create automations', 'edit automations', 'delete automations',

            // Poll permissions
            'view polls', 'create polls', 'edit polls', 'delete polls',

            // Service categories
            'view service categories', 'create service categories', 'edit service categories', 'delete service categories',

            // Service subcategories
            'view service subcategories', 'create service subcategories', 'edit service subcategories', 'delete service subcategories',

            // Service providers
            'view service providers', 'create service providers', 'edit service providers', 'delete service providers',

            // Service provider ratings
            'view service provider ratings', 'create service provider ratings', 'edit service provider ratings', 'delete service provider ratings',
        ];

        collect($permissions)->each(fn(string $name) => Permission::create(['name' => $name]));

        $allPermissions = Permission::all();

        // Admin și sysadmin roles (global, tenant_id = NULL)
        Role::create(['name' => 'admin', 'tenant_id' => null])->givePermissionTo($allPermissions);

        Role::create(['name' => 'sysadmin', 'tenant_id' => null])->givePermissionTo($allPermissions);

        // Roluri globale (tenant_id = NULL)
        Role::create(['name' => 'cex', 'tenant_id' => null])->givePermissionTo([
            'view categories',
            'view tags',
            'view items',

            'view service categories',
            'create service categories',
            'edit service categories',
            'delete service categories',

            'view service subcategories',
            'create service subcategories',
            'edit service subcategories',
            'delete service subcategories',

            'view service providers',
            'create service providers',
            'edit service providers',
            'delete service providers',

            'view service provider ratings',
            'delete service provider ratings',
        ]);

        Role::create(['name' => 'creator', 'tenant_id' => null])->givePermissionTo([
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view tags', 'create tags', 'edit tags', 'delete tags',
            'view items', 'create items', 'edit items', 'delete items',
            'view service categories',
            'view service subcategories',
            'view service providers',
            'view service provider ratings',
        ]);

        Role::create(['name' => 'member', 'tenant_id' => null])->givePermissionTo([
            'view categories',
            'view tags',
            'view items',
            'view service categories',
            'view service subcategories',
            'view service providers',
            'view service provider ratings',
            'create service providers',
            'create service provider ratings',
        ]);

        Role::create(['name' => 'locatar', 'tenant_id' => null])->givePermissionTo([
            'view categories',
            'view tags',
            'view items',
            'view service categories',
            'view service subcategories',
            'view service providers',
            'view service provider ratings',
            'create service providers',
            'create service provider ratings',
        ]);

        Role::create(['name' => 'tehnic', 'tenant_id' => null])->givePermissionTo([
            'view items',
            'create items',
            'edit items',
            'view automations',
            'create automations',
            'edit automations',
            'view service categories',
            'view service subcategories',
            'view service providers',
        ]);

        Role::create(['name' => 'administrație', 'tenant_id' => null])->givePermissionTo([
            'view users',
            'create users',
            'edit users',
            'view items',
            'view categories',
            'view tags',
        ]);
    }
}
