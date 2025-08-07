<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creez tenant-ul principal
        $mainTenant = Tenant::create([
            'name' => 'F1 Atria - Asociația Principală',
            'slug' => 'f1-atria-main',
            'domain' => 'f1.atria.live',
            'description' => 'Asociația principală de proprietari F1 Atria',
            'contact_email' => 'admin@f1.atria.live',
            'contact_phone' => '+40 123 456 789',
            'address' => 'Strada Exemplu, Nr. 123, București',
            'status' => 'active',
            'subscription_plan' => 'enterprise',
            'subscription_expires_at' => now()->addYear(),
            'max_users' => 100,
            'max_automations' => 20,
            'features' => [
                'pool_access',
                'proxy_config',
                'advanced_logging',
                'user_management',
                'role_management',
                'automation_management',
            ],
            'settings' => [
                'timezone' => 'Europe/Bucharest',
                'language' => 'ro',
                'date_format' => 'd.m.Y',
                'time_format' => 'H:i',
            ],
        ]);

        // Creez un alt tenant pentru testare
        $testTenant = Tenant::create([
            'name' => 'Asociația Test - Complexul B',
            'slug' => 'asociatia-test-b',
            'domain' => 'test-b.atria.live',
            'description' => 'Asociația de proprietari pentru Complexul B',
            'contact_email' => 'admin@test-b.atria.live',
            'contact_phone' => '+40 987 654 321',
            'address' => 'Strada Test, Nr. 456, București',
            'status' => 'active',
            'subscription_plan' => 'premium',
            'subscription_expires_at' => now()->addMonths(6),
            'max_users' => 50,
            'max_automations' => 10,
            'features' => [
                'pool_access',
                'basic_logging',
                'user_management',
            ],
            'settings' => [
                'timezone' => 'Europe/Bucharest',
                'language' => 'ro',
                'date_format' => 'd.m.Y',
                'time_format' => 'H:i',
            ],
        ]);

        // Creez un al treilea tenant
        $thirdTenant = Tenant::create([
            'name' => 'Asociația Premium - Complexul C',
            'slug' => 'asociatia-premium-c',
            'domain' => 'premium-c.atria.live',
            'description' => 'Asociația premium de proprietari pentru Complexul C',
            'contact_email' => 'admin@premium-c.atria.live',
            'contact_phone' => '+40 555 123 456',
            'address' => 'Strada Premium, Nr. 789, București',
            'status' => 'active',
            'subscription_plan' => 'enterprise',
            'subscription_expires_at' => now()->addYear(),
            'max_users' => 200,
            'max_automations' => 30,
            'features' => [
                'pool_access',
                'proxy_config',
                'advanced_logging',
                'user_management',
                'role_management',
                'automation_management',
                'advanced_analytics',
                'mobile_app',
            ],
            'settings' => [
                'timezone' => 'Europe/Bucharest',
                'language' => 'ro',
                'date_format' => 'd.m.Y',
                'time_format' => 'H:i',
            ],
        ]);

        // Creez sysadmin-ul (fără tenant)
        $sysadmin = User::create([
            'name' => 'System Administrator',
            'email' => 'sysadmin@f1.atria.live',
            'password' => Hash::make('sysadmin123'),
            'role' => 'sysadmin',
            'status' => 'active',
        ]);

        // Creez un admin pentru tenant-ul principal
        $mainAdmin = User::create([
            'name' => 'Alexandru Popescu',
            'email' => 'alexandru.popescu@f1.atria.live',
            'password' => Hash::make('admin123'),
            'tenant_id' => $mainTenant->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Creez un tenantadmin pentru tenant-ul principal
        $mainTenantAdmin = User::create([
            'name' => 'Elena Dumitrescu',
            'email' => 'elena.dumitrescu@f1.atria.live',
            'password' => Hash::make('tenantadmin123'),
            'tenant_id' => $mainTenant->id,
            'role' => 'tenantadmin',
            'status' => 'active',
        ]);

        // Creez un admin pentru tenant-ul de test
        $testAdmin = User::create([
            'name' => 'Maria Ionescu',
            'email' => 'maria.ionescu@test-b.atria.live',
            'password' => Hash::make('admin123'),
            'tenant_id' => $testTenant->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Creez un tenantadmin pentru tenant-ul de test
        $testTenantAdmin = User::create([
            'name' => 'Vasile Marin',
            'email' => 'vasile.marin@test-b.atria.live',
            'password' => Hash::make('tenantadmin123'),
            'tenant_id' => $testTenant->id,
            'role' => 'tenantadmin',
            'status' => 'active',
        ]);

        // Creez un admin pentru tenant-ul premium
        $premiumAdmin = User::create([
            'name' => 'Ion Vasilescu',
            'email' => 'ion.vasilescu@premium-c.atria.live',
            'password' => Hash::make('admin123'),
            'tenant_id' => $thirdTenant->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Creez un tenantadmin pentru tenant-ul premium
        $premiumTenantAdmin = User::create([
            'name' => 'Ana Popa',
            'email' => 'ana.popa@premium-c.atria.live',
            'password' => Hash::make('tenantadmin123'),
            'tenant_id' => $thirdTenant->id,
            'role' => 'tenantadmin',
            'status' => 'active',
        ]);

        $this->command->info('Tenant-uri și utilizatori creați cu succes!');
        $this->command->info('Sysadmin: sysadmin@f1.atria.live / sysadmin123');
        $this->command->info('Main Admin: alexandru.popescu@f1.atria.live / admin123');
        $this->command->info('Main TenantAdmin: elena.dumitrescu@f1.atria.live / tenantadmin123');
        $this->command->info('Test Admin: maria.ionescu@test-b.atria.live / admin123');
        $this->command->info('Test TenantAdmin: vasile.marin@test-b.atria.live / tenantadmin123');
        $this->command->info('Premium Admin: ion.vasilescu@premium-c.atria.live / admin123');
        $this->command->info('Premium TenantAdmin: ana.popa@premium-c.atria.live / tenantadmin123');
    }
}
