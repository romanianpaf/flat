<?php

namespace Database\Seeders;

use App\Models\Automation;
use App\Models\TenantMqttBroker;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestAutomationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Găsim utilizatorul sysadmin
        $user = User::whereHas('roles', function ($q) {
            $q->where('name', 'sysadmin');
        })->first();

        if (!$user || !$user->tenant_id) {
            $this->command->info('Nu s-a găsit un utilizator sysadmin cu tenant.');
            return;
        }

        // Verificăm dacă există deja
        $existing = Automation::where('tenant_id', $user->tenant_id)
            ->where('mqtt_topic', 'test/ping')
            ->first();

        if ($existing) {
            $this->command->info('Automatizarea test/ping există deja (ID: ' . $existing->id . ')');
            return;
        }

        // Găsim broker-ul implicit pentru tenant
        $broker = TenantMqttBroker::where('tenant_id', $user->tenant_id)
            ->where('is_default', true)
            ->first();

        $automation = Automation::create([
            'name' => 'Test Ping',
            'description' => 'Automatizare de test pentru debug MQTT. Topic: test/ping',
            'type' => 'sensor',
            'mqtt_broker_id' => $broker?->id,
            'use_custom_broker' => false,
            'mqtt_topic' => 'test/ping',
            'mqtt_payload_on' => 'ping',
            'mqtt_payload_off' => 'pong',
            'mqtt_qos' => 0,
            'is_active' => true,
            'tenant_id' => $user->tenant_id,
        ]);

        $this->command->info('Automatizare creată: ' . $automation->name . ' (ID: ' . $automation->id . ')');
    }
}
