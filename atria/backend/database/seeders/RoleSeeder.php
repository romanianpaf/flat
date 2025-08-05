<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'administratie',
                'display_name' => 'Administrație',
                'description' => 'Acces dedicat reprezentanților serviciului de administrație',
                'is_active' => true,
            ],
            [
                'name' => 'sysadmin',
                'display_name' => 'Administrator de sistem',
                'description' => 'Acces complet la toate funcționalitățile sistemului',
                'is_active' => true,
            ],
            [
                'name' => 'cex',
                'display_name' => 'CEx',
                'description' => 'Rol pentru preşedintele și comitetului executiv al Asociației de proprietari',
                'is_active' => true,
            ],
            [
                'name' => 'locatar',
                'display_name' => 'Locatar',
                'description' => 'Rol pentru locatari',
                'is_active' => true,
            ],
            [
                'name' => 'tehnic',
                'display_name' => 'Tehnic',
                'description' => 'Acces la automatizări și tichete pentru serviciul tehnic',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
