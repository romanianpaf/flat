<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();

        // Sysadmin - asignat la tenantul System (id=1)
        User::create([
            'name' => 'Alexandru Popescu',
            'email' => 'alexandru@popescu.vip',
            'password' => 'password',
            'tenant_id' => 1,
        ])->assignRole('sysadmin');

        // Tenanții
        $tenants = \App\Models\Tenant::where('id', '>', 1)->get();

        foreach ($tenants as $tenant) {
            // Admin pentru fiecare tenant
            User::create([
                'name' => 'Admin ' . $tenant->name,
                'email' => 'admin-' . strtolower(str_replace(' ', '-', $tenant->name)) . '@example.com',
                'password' => 'password',
                'tenant_id' => $tenant->id,
            ])->assignRole('admin');

            // 3 CEX pentru fiecare tenant
            for ($i = 1; $i <= 3; $i++) {
                User::create([
                    'name' => 'CEX ' . $i . ' ' . $tenant->name,
                    'email' => 'cex-' . $i . '-' . strtolower(str_replace(' ', '-', $tenant->name)) . '@example.com',
                    'password' => 'password',
                    'tenant_id' => $tenant->id,
                ])->assignRole('cex');
            }

            // 1 Tehnic pentru fiecare tenant
            User::create([
                'name' => 'Tehnic ' . $tenant->name,
                'email' => 'tehnic-' . strtolower(str_replace(' ', '-', $tenant->name)) . '@example.com',
                'password' => 'password',
                'tenant_id' => $tenant->id,
            ])->assignRole('tehnic');

            // 1 Administrație pentru fiecare tenant
            User::create([
                'name' => 'Administrație ' . $tenant->name,
                'email' => 'admin-sec-' . strtolower(str_replace(' ', '-', $tenant->name)) . '@example.com',
                'password' => 'password',
                'tenant_id' => $tenant->id,
            ])->assignRole('administrație');

            // 3 Locatari pentru fiecare tenant
            for ($i = 1; $i <= 3; $i++) {
                User::create([
                    'name' => 'Locatar ' . $i . ' ' . $tenant->name,
                    'email' => 'locatar-' . $i . '-' . strtolower(str_replace(' ', '-', $tenant->name)) . '@example.com',
                    'password' => 'password',
                    'tenant_id' => $tenant->id,
                ])->assignRole('locatar');
            }
        }
    }
}
