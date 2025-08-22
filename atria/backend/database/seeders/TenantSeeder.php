<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Models\Automation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creez rolurile globale
        $this->createGlobalRoles();
        
        // Creez tenanții
        $tenants = $this->createTenants();
        
        // Creez rolurile pentru fiecare tenant
        $this->createTenantRoles($tenants);
        
        // Creez automatizările pentru fiecare tenant
        $this->createAutomations($tenants);
        
        // Creez utilizatorii
        $this->createUsers($tenants);
        
        $this->command->info('Seeder completat cu succes!');
        $this->command->info('Verifică fișierul de log pentru parolele generate.');
    }
    
    private function createGlobalRoles()
    {
        // Verific dacă rolul sysadmin există deja global
        if (!Role::where('name', 'sysadmin')->whereNull('tenant_id')->exists()) {
            // Rol sysadmin - acces la toți tenanții, totul din aplicație
            Role::create([
                'name' => 'sysadmin',
                'display_name' => 'System Administrator',
                'description' => 'Acces complet la toți tenanții și toate funcționalitățile aplicației',
                'tenant_id' => null,
                'is_active' => true,
                'permissions' => [
                    'tenant_management',
                    'user_management',
                    'role_management',
                    'automation_management',
                    'system_settings',
                    'all_tenants_access'
                ]
            ]);
            $this->command->info('Rol sysadmin creat cu succes!');
        } else {
            $this->command->info('Rol sysadmin există deja!');
        }
        
        $this->command->info('Roluri globale verificate cu succes!');
    }
    
    private function createTenants()
    {
        $tenants = [];
        
        // Verific dacă tenanții există deja
        $existingTenants = Tenant::whereIn('slug', ['atria-faza-1', 'atria-faza-2', 'atria-faza-3'])->get()->keyBy('slug');
        
        // Atria Faza 1 - Premium
        if (!$existingTenants->has('atria-faza-1')) {
            $tenants['faza1'] = Tenant::create([
                'name' => 'Atria Faza 1',
                'slug' => 'atria-faza-1',
                'domain' => 'faza1.atria.live',
                'description' => 'Complexul rezidențial Atria Faza 1',
                'contact_email' => 'admin@faza1.atria.live',
                'contact_phone' => '+40 123 456 789',
                'address' => 'Strada Atria, Nr. 1, București',
                'status' => 'active',
                'subscription_plan' => 'premium',
                'subscription_expires_at' => now()->addYear(),
                'max_users' => 50,
                'max_automations' => 15,
                'features' => [
                    'pool_access',
                    'barrier_access',
                    'building_access',
                    'visitor_parking',
                    'user_management',
                    'role_management',
                    'automation_management'
                ],
                'settings' => [
                    'timezone' => 'Europe/Bucharest',
                    'language' => 'ro',
                    'date_format' => 'd.m.Y',
                    'time_format' => 'H:i',
                ],
            ]);
            $this->command->info('Tenant Atria Faza 1 creat cu succes!');
        } else {
            $tenants['faza1'] = $existingTenants['atria-faza-1'];
            $this->command->info('Tenant Atria Faza 1 există deja!');
        }
        
        // Atria Faza 2 - Premium
        if (!$existingTenants->has('atria-faza-2')) {
            $tenants['faza2'] = Tenant::create([
                'name' => 'Atria Faza 2',
                'slug' => 'atria-faza-2',
                'domain' => 'faza2.atria.live',
                'description' => 'Complexul rezidențial Atria Faza 2',
                'contact_email' => 'admin@faza2.atria.live',
                'contact_phone' => '+40 123 456 790',
                'address' => 'Strada Atria, Nr. 2, București',
                'status' => 'active',
                'subscription_plan' => 'premium',
                'subscription_expires_at' => now()->addYear(),
                'max_users' => 50,
                'max_automations' => 15,
                'features' => [
                    'pool_access',
                    'barrier_access',
                    'building_access',
                    'user_management',
                    'role_management',
                    'automation_management'
                ],
                'settings' => [
                    'timezone' => 'Europe/Bucharest',
                    'language' => 'ro',
                    'date_format' => 'd.m.Y',
                    'time_format' => 'H:i',
                ],
            ]);
            $this->command->info('Tenant Atria Faza 2 creat cu succes!');
        } else {
            $tenants['faza2'] = $existingTenants['atria-faza-2'];
            $this->command->info('Tenant Atria Faza 2 există deja!');
        }
        
        // Atria Faza 3 - Premium
        if (!$existingTenants->has('atria-faza-3')) {
            $tenants['faza3'] = Tenant::create([
                'name' => 'Atria Faza 3',
                'slug' => 'atria-faza-3',
                'domain' => 'faza3.atria.live',
                'description' => 'Complexul rezidențial Atria Faza 3',
                'contact_email' => 'admin@faza3.atria.live',
                'contact_phone' => '+40 123 456 791',
            'address' => 'Strada Atria, Nr. 3, București',
                'status' => 'active',
                'subscription_plan' => 'premium',
                'subscription_expires_at' => now()->addYear(),
                'max_users' => 50,
                'max_automations' => 15,
                'features' => [
                    'pool_access',
                    'barrier_access',
                    'building_access',
                    'user_management',
                    'role_management',
                    'automation_management'
                ],
                'settings' => [
                    'timezone' => 'Europe/Bucharest',
                    'language' => 'ro',
                    'date_format' => 'd.m.Y',
                    'time_format' => 'H:i',
                ],
            ]);
            $this->command->info('Tenant Atria Faza 3 creat cu succes!');
        } else {
            $tenants['faza3'] = $existingTenants['atria-faza-3'];
            $this->command->info('Tenant Atria Faza 3 există deja!');
        }
        
        $this->command->info('Tenanții verificați cu succes!');
        return $tenants;
    }
    
    private function createTenantRoles($tenants)
    {
        foreach ($tenants as $tenant) {
            // Verific dacă rolurile există deja pentru acest tenant
            $existingRoles = Role::where('tenant_id', $tenant->id)->get()->keyBy('name');
            
            // Verific dacă rolurile există deja global
            $globalRoles = Role::whereNull('tenant_id')->get()->keyBy('name');
            
            // Rol cex - acces pe tot ce ține de un tenant: roluri, utilizatori, automatizări etc
            if (!Role::where('name', 'cex')->where('tenant_id', $tenant->id)->exists() && !Role::where('name', 'cex')->whereNull('tenant_id')->exists()) {
                Role::create([
                    'name' => 'cex',
                    'display_name' => 'Chief Executive',
                    'description' => 'Acces complet pe tenant: roluri, utilizatori, automatizări, etc.',
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                    'permissions' => [
                        'user_management',
                        'role_management',
                        'automation_management',
                        'tenant_settings',
                        'reports_access',
                        'full_tenant_access'
                    ]
                ]);
                $this->command->info("Rol cex creat pentru {$tenant->name}!");
            } else {
                $this->command->info("Rol cex există deja pentru {$tenant->name}!");
            }
            
            // Rol admin - acces pentru firma de administrație: utilizatori, altele
            if (!Role::where('name', 'admin')->where('tenant_id', $tenant->id)->exists()) {
                Role::create([
                    'name' => 'admin',
                    'display_name' => 'Administrator',
                    'description' => 'Acces pentru firma de administrație: utilizatori, setări de bază',
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                    'permissions' => [
                        'user_management',
                        'basic_tenant_settings',
                        'reports_access',
                        'limited_tenant_access'
                    ]
                ]);
                $this->command->info("Rol admin creat pentru {$tenant->name}!");
            } else {
                $this->command->info("Rol admin există deja pentru {$tenant->name}!");
            }
            
            // Rol tehnic - acces pe automatizările unui tenant
            if (!Role::where('name', 'tehnic')->where('tenant_id', $tenant->id)->exists() && !Role::where('name', 'tehnic')->whereNull('tenant_id')->exists()) {
                Role::create([
                    'name' => 'tehnic',
                    'display_name' => 'Tehnic',
                    'description' => 'Acces pe automatizările tenant-ului',
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                    'permissions' => [
                        'automation_view',
                        'automation_control',
                        'automation_logs',
                        'limited_tenant_access'
                    ]
                ]);
                $this->command->info("Rol tehnic creat pentru {$tenant->name}!");
            } else {
                $this->command->info("Rol tehnic există deja pentru {$tenant->name}!");
            }
            
            // Rol locatar - acces de view only deocamdată pe un tenant
            if (!Role::where('name', 'locatar')->where('tenant_id', $tenant->id)->exists() && !Role::where('name', 'locatar')->whereNull('tenant_id')->exists()) {
                Role::create([
                    'name' => 'locatar',
                    'display_name' => 'Locatar',
                    'description' => 'Acces de view only pe tenant',
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                    'permissions' => [
                        'tenant_view',
                        'limited_reports_access',
                        'read_only_access'
                    ]
                ]);
                $this->command->info("Rol locatar creat pentru {$tenant->name}!");
            } else {
                $this->command->info("Rol locatar există deja pentru {$tenant->name}!");
            }
        }
        
        $this->command->info('Roluri pentru tenanți verificate cu succes!');
    }
    
        private function createAutomations($tenants)
    {
        foreach ($tenants as $key => $tenant) {
            // Verific dacă automatizările există deja pentru acest tenant
            $existingAutomations = Automation::where('tenant_id', $tenant->id)->get()->keyBy('name');
            
            // Acces barieră pentru toți tenanții
            $barrierName = 'Acces Barieră ' . $tenant->name;
            if (!$existingAutomations->has($barrierName)) {
                Automation::create([
                    'name' => $barrierName,
                    'type' => 'barrier_access',
                    'mqtt_broker' => 'mqtt.atria.live',
                    'mqtt_port' => 1883,
                    'mqtt_username' => 'barrier_' . $tenant->slug,
                    'mqtt_password' => Str::random(16),
                    'mqtt_topic_control' => 'atria/' . $tenant->slug . '/barrier/control',
                    'mqtt_topic_status' => 'atria/' . $tenant->slug . '/barrier/status',
                    'esp_device_id' => 'barrier_' . $tenant->slug . '_001',
                    'lock_relay_pin' => 5,
                    'status' => true,
                    'config' => [
                        'auto_close_delay' => 30,
                        'max_open_time' => 300,
                        'emergency_override' => true
                    ],
                    'description' => 'Sistem de control acces barieră pentru ' . $tenant->name,
                    'tenant_id' => $tenant->id,
                ]);
                $this->command->info("Automatizare barieră creată pentru {$tenant->name}!");
            } else {
                $this->command->info("Automatizare barieră există deja pentru {$tenant->name}!");
            }
            
            // Acces piscină pentru toți tenanții
            $poolName = 'Acces Piscină ' . $tenant->name;
            if (!$existingAutomations->has($poolName)) {
                Automation::create([
                    'name' => $poolName,
                    'type' => 'pool_access',
                    'mqtt_broker' => 'mqtt.atria.live',
                    'mqtt_port' => 1883,
                    'mqtt_username' => 'pool_' . $tenant->slug,
                    'mqtt_password' => Str::random(16),
                    'mqtt_topic_control' => 'atria/' . $tenant->slug . '/pool/control',
                    'mqtt_topic_status' => 'atria/' . $tenant->slug . '/pool/status',
                    'esp_device_id' => 'pool_' . $tenant->slug . '_001',
                    'lock_relay_pin' => 6,
                    'status' => true,
                    'config' => [
                        'pool_hours' => '06:00-22:00',
                        'max_capacity' => 20,
                        'maintenance_mode' => false
                    ],
                    'description' => 'Sistem de control acces piscină pentru ' . $tenant->name,
                    'tenant_id' => $tenant->id,
                ]);
                $this->command->info("Automatizare piscină creată pentru {$tenant->name}!");
            } else {
                $this->command->info("Automatizare piscină există deja pentru {$tenant->name}!");
            }
            
            // Acces imobil pentru toți tenanții
            $buildingName = 'Acces Imobil ' . $tenant->name;
            if (!$existingAutomations->has($buildingName)) {
                Automation::create([
                    'name' => $buildingName,
                    'type' => 'building_access',
                    'mqtt_broker' => 'mqtt.atria.live',
                    'mqtt_port' => 1883,
                    'mqtt_username' => 'building_' . $tenant->slug,
                    'mqtt_password' => Str::random(16),
                    'mqtt_topic_control' => 'atria/' . $tenant->slug . '/building/control',
                    'mqtt_topic_status' => 'atria/' . $tenant->slug . '/building/status',
                    'esp_device_id' => 'building_' . $tenant->slug . '_001',
                    'lock_relay_pin' => 7,
                    'status' => true,
                    'config' => [
                        'access_hours' => '00:00-23:59',
                        'guest_access' => true,
                        'emergency_exit' => true
                    ],
                    'description' => 'Sistem de control acces imobil pentru ' . $tenant->name,
                    'tenant_id' => $tenant->id,
                ]);
                $this->command->info("Automatizare imobil creată pentru {$tenant->name}!");
            } else {
                $this->command->info("Automatizare imobil există deja pentru {$tenant->name}!");
            }
            
            // Acces parcare vizitatori (doar pentru Atria Faza 1)
            if ($key === 'faza1') {
                $parkingName = 'Acces Parcare Vizitatori ' . $tenant->name;
                if (!$existingAutomations->has($parkingName)) {
                    Automation::create([
                        'name' => $parkingName,
                        'type' => 'visitor_parking',
                        'mqtt_broker' => 'mqtt.atria.live',
                        'mqtt_port' => 1883,
                        'mqtt_username' => 'parking_' . $tenant->slug,
                        'mqtt_password' => Str::random(16),
                        'mqtt_topic_control' => 'atria/' . $tenant->slug . '/parking/control',
                        'mqtt_topic_status' => 'atria/' . $tenant->slug . '/parking/status',
                        'esp_device_id' => 'parking_' . $tenant->slug . '_001',
                        'lock_relay_pin' => 8,
                        'status' => true,
                        'config' => [
                            'total_spots' => 12,
                            'max_duration' => 720, // 12 ore în minute
                            'overnight_allowed' => false,
                            'reservation_system' => true
                        ],
                        'description' => 'Sistem de control parcare vizitatori (12 locuri) pentru ' . $tenant->name,
                        'tenant_id' => $tenant->id,
                    ]);
                    $this->command->info("Automatizare parcare vizitatori creată pentru {$tenant->name}!");
                } else {
                    $this->command->info("Automatizare parcare vizitatori există deja pentru {$tenant->name}!");
                }
            }
        }
        
        $this->command->info('Automatizări verificate cu succes!');
    }
    
    private function createUsers($tenants)
    {
        $passwords = [];
        
        // Verific dacă sysadmin-ul există deja
        if (!User::where('email', 'sysadmin@f1.atria.live')->exists()) {
            // Creez sysadmin-ul (fără tenant)
            $sysadminPassword = Str::random(12);
            $sysadmin = User::create([
                'name' => 'System Administrator',
                'email' => 'sysadmin@f1.atria.live',
                'password' => Hash::make($sysadminPassword),
                'role' => 'sysadmin',
                'status' => 'active',
                'tenant_id' => null,
            ]);
            
            $passwords[] = [
                'name' => 'System Administrator',
                'email' => 'sysadmin@f1.atria.live',
                'password' => $sysadminPassword,
                'role' => 'sysadmin'
            ];
            $this->command->info('Sysadmin creat cu succes!');
        } else {
            $this->command->info('Sysadmin există deja!');
        }
        
        // Creez utilizatorii pentru fiecare tenant
        foreach ($tenants as $key => $tenant) {
            // Verific utilizatorii existenți pentru acest tenant
            $existingUsers = User::where('tenant_id', $tenant->id)->get()->keyBy('email');
            
            // 1 admin
            $adminEmail = 'admin@' . $tenant->domain;
            if (!$existingUsers->has($adminEmail)) {
                $adminPassword = Str::random(12);
                $admin = User::create([
                    'name' => 'Administrator ' . $tenant->name,
                    'email' => $adminEmail,
                    'password' => Hash::make($adminPassword),
                    'role' => 'admin',
                    'status' => 'active',
                    'tenant_id' => $tenant->id,
                ]);
                
                $passwords[] = [
                    'name' => 'Administrator ' . $tenant->name,
                    'email' => $adminEmail,
                    'password' => $adminPassword,
                    'role' => 'admin'
                ];
                $this->command->info("Administrator creat pentru {$tenant->name}!");
            } else {
                $this->command->info("Administrator există deja pentru {$tenant->name}!");
            }
            
            // 1 tehnic
            $tehnicEmail = 'tehnic@' . $tenant->domain;
            if (!$existingUsers->has($tehnicEmail)) {
                $tehnicPassword = Str::random(12);
                $tehnic = User::create([
                    'name' => 'Tehnic ' . $tenant->name,
                    'email' => $tehnicEmail,
                    'password' => Hash::make($tehnicPassword),
                    'role' => 'tehnic',
                    'status' => 'active',
                    'tenant_id' => $tenant->id,
                ]);
                
                $passwords[] = [
                    'name' => 'Tehnic ' . $tenant->name,
                    'email' => $tehnicEmail,
                    'password' => $tehnicPassword,
                    'role' => 'tehnic'
                ];
                $this->command->info("Tehnic creat pentru {$tenant->name}!");
            } else {
                $this->command->info("Tehnic există deja pentru {$tenant->name}!");
            }
            
            // 2 cex
            for ($i = 1; $i <= 2; $i++) {
                $cexEmail = 'cex' . $i . '@' . $tenant->domain;
                if (!$existingUsers->has($cexEmail)) {
                    $cexPassword = Str::random(12);
                    $cex = User::create([
                        'name' => 'CEX ' . $i . ' ' . $tenant->name,
                        'email' => $cexEmail,
                        'password' => Hash::make($cexPassword),
                        'role' => 'cex',
                        'status' => 'active',
                        'tenant_id' => $tenant->id,
                    ]);
                    
                    $passwords[] = [
                        'name' => 'CEX ' . $i . ' ' . $tenant->name,
                        'email' => $cexEmail,
                        'password' => $cexPassword,
                        'role' => 'cex'
                    ];
                    $this->command->info("CEX {$i} creat pentru {$tenant->name}!");
                } else {
                    $this->command->info("CEX {$i} există deja pentru {$tenant->name}!");
                }
            }
            
            // 3 locatari
            for ($i = 1; $i <= 3; $i++) {
                $locatarEmail = 'locatar' . $i . '@' . $tenant->domain;
                if (!$existingUsers->has($locatarEmail)) {
                    $locatarPassword = Str::random(12);
                    $locatar = User::create([
                        'name' => 'Locatar ' . $i . ' ' . $tenant->name,
                        'email' => $locatarEmail,
                        'password' => Hash::make($locatarPassword),
                        'role' => 'locatar',
                        'status' => 'active',
                        'tenant_id' => $tenant->id,
                    ]);
                    
                    $passwords[] = [
                        'name' => 'Locatar ' . $i . ' ' . $tenant->name,
                        'email' => $locatarEmail,
                        'password' => $locatarPassword,
                        'role' => 'locatar'
                    ];
                    $this->command->info("Locatar {$i} creat pentru {$tenant->name}!");
                } else {
                    $this->command->info("Locatar {$i} există deja pentru {$tenant->name}!");
                }
            }
        }
        
        if (empty($passwords)) {
            $this->command->info('Toți utilizatorii există deja!');
            return;
        }
        
        // Afișez parolele generate
        $this->command->info('Utilizatori noi creați cu succes!');
        $this->command->info('Parolele generate sunt:');
        $this->command->info('=====================================');
        
        foreach ($passwords as $user) {
            $this->command->info(sprintf(
                '%-25s | %-30s | %-15s | %s',
                $user['name'],
                $user['email'],
                $user['role'],
                $user['password']
            ));
        }
        
        $this->command->info('=====================================');
        $this->command->info('Salvează aceste parole într-un loc sigur!');
        
        // Salvez parolele într-un fișier de log
        $logContent = "Parole generate la: " . now() . "\n";
        $logContent .= "=====================================\n";
        foreach ($passwords as $user) {
            $logContent .= sprintf(
                '%-25s | %-30s | %-15s | %s',
                $user['name'],
                $user['email'],
                $user['role'],
                $user['password']
            ) . "\n";
        }
        $logContent .= "=====================================\n";
        
        file_put_contents(storage_path('logs/generated_passwords.log'), $logContent);
        $this->command->info('Parolele au fost salvate în: storage/logs/generated_passwords.log');
    }
}
