<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Automation;

class AutomationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pool Access Automation
        Automation::create([
            'name' => 'Acces Piscină - Zăvor Electromagnetic',
            'type' => 'pool_access',
            'mqtt_broker' => '192.168.1.100',
            'mqtt_port' => 1883,
            'mqtt_username' => 'pool_user',
            'mqtt_password' => 'pool_password_123',
            'mqtt_topic_control' => 'pool/lock/control',
            'mqtt_topic_status' => 'pool/lock/status',
            'esp_device_id' => 'ESP32_POOL_001',
            'lock_relay_pin' => 26,
            'status' => true,
            'config' => [
                'unlock_duration' => 5000, // 5 seconds
                'auto_lock_delay' => 30000, // 30 seconds
                'max_unlock_attempts' => 3,
                'lock_type' => 'electromagnetic',
                'voltage' => 12,
                'current' => 0.5,
            ],
            'description' => 'Sistem de control pentru zăvorul electromagnetic de la piscină. Permite deschiderea și închiderea automată a ușii de acces.',
        ]);

        // Proxy Configuration (for future use)
        Automation::create([
            'name' => 'Proxy Local - MiniPC',
            'type' => 'proxy',
            'mqtt_broker' => '192.168.1.50',
            'mqtt_port' => 1883,
            'mqtt_username' => 'proxy_user',
            'mqtt_password' => 'proxy_password_123',
            'mqtt_topic_control' => 'proxy/control',
            'mqtt_topic_status' => 'proxy/status',
            'esp_device_id' => null,
            'lock_relay_pin' => null,
            'status' => false, // Disabled by default
            'config' => [
                'proxy_port' => 8080,
                'proxy_type' => 'http',
                'max_connections' => 100,
                'cache_enabled' => true,
                'cache_size' => '1GB',
            ],
            'description' => 'Configurație pentru proxy local pe MiniPC. Permite gestionarea traficului de rețea și cache-ul local.',
        ]);

        // Additional automation example
        Automation::create([
            'name' => 'Iluminat Exterior',
            'type' => 'other',
            'mqtt_broker' => '192.168.1.100',
            'mqtt_port' => 1883,
            'mqtt_username' => 'lighting_user',
            'mqtt_password' => 'lighting_password_123',
            'mqtt_topic_control' => 'lighting/control',
            'mqtt_topic_status' => 'lighting/status',
            'esp_device_id' => 'ESP32_LIGHTING_001',
            'lock_relay_pin' => null,
            'status' => true,
            'config' => [
                'auto_on_time' => '18:00',
                'auto_off_time' => '06:00',
                'motion_sensor_enabled' => true,
                'brightness_levels' => [25, 50, 75, 100],
            ],
            'description' => 'Sistem de control pentru iluminatul exterior. Include control automat bazat pe oră și senzori de mișcare.',
        ]);

        $this->command->info('Automatizări de test create cu succes!');
    }
}
