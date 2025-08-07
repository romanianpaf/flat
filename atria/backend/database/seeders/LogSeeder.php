<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;

class LogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        
        if (!$user) {
            return;
        }

        // Loguri de test pentru diferite acțiuni
        $logs = [
            [
                'user_id' => $user->id,
                'action' => 'user_login',
                'description' => "Utilizatorul {$user->name} s-a autentificat cu succes",
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'level' => 'success',
                'metadata' => ['user_id' => $user->id, 'email' => $user->email],
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'user_id' => $user->id,
                'action' => 'users_listed',
                'description' => 'Lista de utilizatori a fost vizualizată',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'level' => 'info',
                'metadata' => ['count' => 5],
                'created_at' => Carbon::now()->subHours(1),
            ],
            [
                'user_id' => $user->id,
                'action' => 'user_created',
                'description' => 'Utilizator nou creat: John Doe',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'level' => 'success',
                'metadata' => ['user_id' => 2, 'email' => 'john@example.com'],
                'created_at' => Carbon::now()->subMinutes(45),
            ],
            [
                'user_id' => $user->id,
                'action' => 'role_updated',
                'description' => 'Rol actualizat: Administrator',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'level' => 'success',
                'metadata' => ['role_id' => 1, 'old_data' => [], 'new_data' => []],
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'user_id' => $user->id,
                'action' => 'user_logout',
                'description' => "Utilizatorul {$user->name} s-a deconectat",
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'level' => 'info',
                'metadata' => ['user_id' => $user->id],
                'created_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'user_id' => null,
                'action' => 'get_admin_dashboard',
                'description' => 'Guest a făcut o cerere GET la admin/dashboard (Status: 401)',
                'ip_address' => '203.0.113.1',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
                'level' => 'warning',
                'metadata' => ['method' => 'GET', 'url' => '/admin/dashboard', 'status_code' => 401],
                'created_at' => Carbon::now()->subMinutes(10),
            ],
            [
                'user_id' => null,
                'action' => 'post_login_failed',
                'description' => 'Guest a făcut o cerere POST la api/login (Status: 422)',
                'ip_address' => '203.0.113.1',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
                'level' => 'warning',
                'metadata' => ['method' => 'POST', 'url' => '/api/login', 'status_code' => 422],
                'created_at' => Carbon::now()->subMinutes(5),
            ],
        ];

        foreach ($logs as $logData) {
            Log::create($logData);
        }

        $this->command->info('Loguri de test create cu succes!');
    }
}
