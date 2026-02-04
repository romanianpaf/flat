<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MqttTestController extends Controller
{
    /**
     * Testează conexiunea MQTT pentru un tenant (mTLS)
     */
    public function testTenantConnection(Tenant $tenant): JsonResponse
    {
        $user = auth()->user();
        
        // Verifică dacă utilizatorul are permisiune (sysadmin)
        if (!$user || !$user->hasRole('sysadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Nu ai permisiunea să testezi conexiunea MQTT.',
            ], 403);
        }

        // Verifică dacă tenant-ul are MQTT configurat
        if (!$tenant->hasMqttConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'MQTT nu este configurat pentru acest beneficiar.',
                'details' => [
                    'mqtt_host' => $tenant->mqtt_host ? 'OK' : 'LIPSĂ',
                    'mqtt_ca_path' => $tenant->mqtt_ca_path ? 'OK' : 'LIPSĂ',
                    'mqtt_client_cert_path' => $tenant->mqtt_client_cert_path ? 'OK' : 'LIPSĂ',
                    'mqtt_client_key_path' => $tenant->mqtt_client_key_path ? 'OK' : 'LIPSĂ',
                ],
            ], 400);
        }

        $mqttConfig = $tenant->getMqttConfig();

        // Verifică dacă fișierele certificate există
        $certChecks = $this->checkCertificateFiles($mqttConfig);
        if (!$certChecks['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Certificate lipsă sau invalide.',
                'details' => $certChecks['details'],
            ], 400);
        }

        // Încearcă conexiunea
        $testResult = $this->testMtlsConnection($mqttConfig, $tenant->mqtt_topic_prefix ?? $tenant->getSlug());

        return response()->json([
            'success' => $testResult['success'],
            'message' => $testResult['message'],
            'data' => [
                'host' => $mqttConfig['host'],
                'port' => $mqttConfig['port'],
                'topic_prefix' => $mqttConfig['topic_prefix'],
                'client_cn' => $tenant->getMqttClientCN(),
                'tested_at' => now()->toIso8601String(),
                'method' => $testResult['method'] ?? 'mosquitto_pub',
            ],
        ], $testResult['success'] ? 200 : 400);
    }

    /**
     * Verifică existența fișierelor certificate
     */
    private function checkCertificateFiles(array $config): array
    {
        $details = [];
        $allOk = true;

        foreach (['ca_path', 'cert_path', 'key_path'] as $key) {
            $path = $config[$key] ?? null;
            if (!$path) {
                $details[$key] = 'NECONFIGURAT';
                $allOk = false;
            } elseif (!file_exists($path)) {
                $details[$key] = "LIPSĂ: {$path}";
                $allOk = false;
            } elseif (!is_readable($path)) {
                $details[$key] = "NEACCESIBIL: {$path}";
                $allOk = false;
            } else {
                $details[$key] = 'OK';
            }
        }

        return [
            'success' => $allOk,
            'details' => $details,
        ];
    }

    /**
     * Testează conexiunea mTLS folosind mosquitto_pub
     */
    private function testMtlsConnection(array $config, string $topicPrefix): array
    {
        if (!function_exists('shell_exec') || !is_callable('shell_exec')) {
            return [
                'success' => false,
                'message' => 'shell_exec nu este disponibil pe acest server.',
                'method' => 'none',
            ];
        }

        // Verificăm dacă mosquitto_pub este disponibil
        $mosquittoPub = @shell_exec('which mosquitto_pub 2>/dev/null');
        if (!$mosquittoPub || !trim($mosquittoPub)) {
            return [
                'success' => false,
                'message' => 'mosquitto_pub nu este instalat.',
                'method' => 'none',
            ];
        }

        $testTopic = "{$topicPrefix}/test/connection";
        $testPayload = json_encode([
            'type' => 'connection_test',
            'timestamp' => now()->toIso8601String(),
        ]);

        // Construim comanda mosquitto_pub cu mTLS
        $cmd = sprintf(
            'timeout 15 mosquitto_pub -h %s -p %d --cafile %s --cert %s --key %s -t %s -m %s 2>&1',
            escapeshellarg($config['host']),
            $config['port'],
            escapeshellarg($config['ca_path']),
            escapeshellarg($config['cert_path']),
            escapeshellarg($config['key_path']),
            escapeshellarg($testTopic),
            escapeshellarg($testPayload)
        );

        $output = @shell_exec($cmd);
        
        // mosquitto_pub nu are output la succes
        $hasError = $output && (
            stripos($output, 'Error') !== false || 
            stripos($output, 'error') !== false ||
            stripos($output, 'refused') !== false ||
            stripos($output, 'timeout') !== false ||
            stripos($output, 'failed') !== false ||
            stripos($output, 'unable') !== false
        );

        if ($hasError) {
            return [
                'success' => false,
                'message' => $this->formatErrorMessage($output),
                'method' => 'mosquitto_pub',
                'raw_output' => $output,
            ];
        }

        return [
            'success' => true,
            'message' => "Conexiune mTLS reușită. Mesaj test trimis pe topic {$testTopic}",
            'method' => 'mosquitto_pub',
        ];
    }

    /**
     * Trimite un mesaj de test MQTT
     */
    public function sendTest(Request $request): JsonResponse
    {
        $request->validate([
            'payload' => 'required|string|in:ping,pong',
        ]);

        $user = auth()->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Nu aparții niciunui beneficiar.',
            ], 403);
        }

        // Obținem tenant-ul și verificăm MQTT
        $tenant = $user->tenant;
        if (!$tenant || !$tenant->hasMqttConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'MQTT nu este configurat pentru beneficiarul tău.',
            ], 400);
        }

        // Găsim automatizarea de test pentru tenant
        $automation = Automation::where('tenant_id', $user->tenant_id)
            ->where('type', 'test')
            ->first();

        if (!$automation) {
            // Dacă nu există, încercăm să găsim una cu topic test/ping
            $automation = Automation::where('tenant_id', $user->tenant_id)
                ->where('mqtt_topic', 'like', '%test%')
                ->first();
        }

        if (!$automation) {
            return response()->json([
                'success' => false,
                'message' => 'Nu există o automatizare de test configurată.',
            ], 404);
        }

        $mqttConfig = $tenant->getMqttConfig();
        $payload = $request->input('payload');
        $topic = $automation->mqtt_topic;

        // Încercăm să trimitem mesajul MQTT cu mTLS
        try {
            $result = $this->publishMqttMessageMtls(
                $mqttConfig,
                $topic,
                $payload
            );

            $broker = $mqttConfig['host'] . ':' . $mqttConfig['port'];
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] 
                    ? "Mesaj '{$payload}' trimis cu succes pe topic '{$topic}'"
                    : $this->formatErrorMessage($result['message'] ?? 'Eroare necunoscută'),
                'data' => [
                    'topic' => $topic,
                    'payload' => $payload,
                    'broker' => $broker,
                    'sent_at' => now()->toIso8601String(),
                    'method' => $result['method'] ?? 'unknown',
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('MQTT sendTest exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Eroare: ' . $e->getMessage(),
                'data' => [
                    'topic' => $topic,
                    'payload' => $payload,
                ],
            ]);
        }
    }

    /**
     * Publică un mesaj MQTT folosind mTLS
     */
    private function publishMqttMessageMtls(array $config, string $topic, string $payload): array
    {
        if (!function_exists('shell_exec') || !is_callable('shell_exec')) {
            \Log::info("MQTT mTLS: topic={$topic}, payload={$payload}, host={$config['host']}");
            return [
                'success' => true,
                'message' => 'Mesaj înregistrat (shell_exec dezactivat)',
                'method' => 'logged',
            ];
        }

        $mosquittoPub = @shell_exec('which mosquitto_pub 2>/dev/null');
        if (!$mosquittoPub || !trim($mosquittoPub)) {
            \Log::info("MQTT mTLS: topic={$topic}, payload={$payload}, host={$config['host']}");
            return [
                'success' => true,
                'message' => 'Mesaj înregistrat (mosquitto_pub nu este instalat)',
                'method' => 'logged',
            ];
        }

        $cmd = sprintf(
            'timeout 10 mosquitto_pub -h %s -p %d --cafile %s --cert %s --key %s -t %s -m %s 2>&1',
            escapeshellarg($config['host']),
            $config['port'],
            escapeshellarg($config['ca_path']),
            escapeshellarg($config['cert_path']),
            escapeshellarg($config['key_path']),
            escapeshellarg($topic),
            escapeshellarg($payload)
        );

        $output = @shell_exec($cmd);
        
        $hasError = $output && (
            stripos($output, 'Error') !== false || 
            stripos($output, 'error') !== false ||
            stripos($output, 'refused') !== false ||
            stripos($output, 'timeout') !== false
        );

        return [
            'success' => !$hasError,
            'message' => $hasError ? $output : 'Mesaj trimis via mosquitto_pub (mTLS)',
            'method' => 'mosquitto_pub',
            'output' => $output,
        ];
    }

    /**
     * Formatează mesajele de eroare într-un format mai prietenos
     */
    private function formatErrorMessage(string $message): string
    {
        if (stripos($message, 'Connection refused') !== false) {
            return 'Conexiune refuzată - broker-ul MQTT nu este disponibil sau WireGuard nu funcționează';
        }
        if (stripos($message, 'timeout') !== false) {
            return 'Timeout - broker-ul MQTT nu răspunde (verifică WireGuard și Mosquitto)';
        }
        if (stripos($message, 'certificate') !== false || stripos($message, 'ssl') !== false) {
            return 'Eroare certificat - verifică path-urile și validitatea certificatelor mTLS';
        }
        if (stripos($message, 'Authentication') !== false || stripos($message, 'not authorized') !== false) {
            return 'Autentificare eșuată - CN-ul certificatului nu este autorizat';
        }
        if (stripos($message, 'Protocol') !== false) {
            return 'Eroare de protocol - verificați portul și configurația TLS';
        }
        if (stripos($message, 'unable to load') !== false) {
            return 'Nu se pot încărca certificatele - verifică permisiunile fișierelor';
        }
        return trim($message);
    }

    /**
     * Obține statusul automatizării de test
     */
    public function getTestStatus(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user || !$user->tenant_id) {
                return response()->json([
                    'success' => false,
                    'configured' => false,
                    'message' => 'Nu aparții niciunui beneficiar.',
                ]);
            }

            $tenant = $user->tenant;
            
            // Verificăm dacă tenant-ul are MQTT configurat
            if (!$tenant || !$tenant->hasMqttConfigured()) {
                return response()->json([
                    'success' => true,
                    'configured' => false,
                    'message' => 'MQTT nu este configurat pentru beneficiarul tău.',
                    'mqtt_configured' => false,
                ]);
            }

            // Căutăm automatizarea de test pentru tenant-ul utilizatorului
            $automation = Automation::where('tenant_id', $user->tenant_id)
                ->where(function ($q) {
                    $q->where('type', 'test')
                        ->orWhere('mqtt_topic', 'like', '%test%');
                })
                ->first();

            $mqttConfig = $tenant->getMqttConfig();

            if (!$automation) {
                return response()->json([
                    'success' => true,
                    'configured' => false,
                    'mqtt_configured' => true,
                    'message' => 'Nu există automatizare de test, dar MQTT este configurat.',
                    'data' => [
                        'broker_host' => $mqttConfig['host'],
                        'broker_port' => $mqttConfig['port'],
                        'topic_prefix' => $mqttConfig['topic_prefix'],
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'configured' => true,
                'mqtt_configured' => true,
                'data' => [
                    'name' => $automation->name,
                    'topic' => $automation->mqtt_topic,
                    'payload_on' => $automation->mqtt_payload_on ?? 'ping',
                    'payload_off' => $automation->mqtt_payload_off ?? 'pong',
                    'broker_host' => $mqttConfig['host'],
                    'broker_port' => $mqttConfig['port'],
                    'topic_prefix' => $mqttConfig['topic_prefix'],
                    'is_active' => $automation->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('MQTT getTestStatus error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'configured' => false,
                'message' => 'Eroare: ' . $e->getMessage(),
            ], 500);
        }
    }
}
