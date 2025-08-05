<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'alex@siteuri.pro',
                'password' => 'Str0ngP@ssw0rd2024!',
                'role_names' => ['sysadmin'],
            ],
            [
                'name' => 'Alexandru Popescu',
                'email' => 'alexandru.popescu@atria.live',
                'password' => 'Spr!ngFr3sh',
                'role_names' => ['cex'],
            ],
            [
                'name' => 'Alexandru Popescu',
                'email' => 'alexandru@popescu.vip',
                'password' => 'S3cur3P@ss2024!',
                'role_names' => ['locatar'],
            ],
        ];

        foreach ($users as $userData) {
            $roleNames = $userData['role_names'];
            unset($userData['role_names']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                ]
            );

            // Get role IDs and assign them
            $roleIds = Role::whereIn('name', $roleNames)->pluck('id')->toArray();
            if (!empty($roleIds)) {
                $user->roles()->sync($roleIds);
            }

            // Output credentials for reference
            $this->command->info("User created: {$user->name} ({$user->email})");
            $this->command->info("Password: {$userData['password']}");
            $this->command->info("Roles: " . implode(', ', $roleNames));
            $this->command->info('---');
        }
    }
}
