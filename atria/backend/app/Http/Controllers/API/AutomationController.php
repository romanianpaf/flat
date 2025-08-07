<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Automation;
use App\Models\PoolAccessLog;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AutomationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $automations = Automation::orderBy('type')->orderBy('name')->get();
        
        // Log the action
        Log::logInfo(
            'automations_listed',
            'Lista de automatizări a fost vizualizată',
            ['count' => $automations->count()]
        );
        
        return response()->json([
            'automations' => $automations,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:pool_access,proxy,other',
            'mqtt_broker' => 'nullable|string|max:255',
            'mqtt_port' => 'nullable|integer|min:1|max:65535',
            'mqtt_username' => 'nullable|string|max:255',
            'mqtt_password' => 'nullable|string|max:255',
            'mqtt_topic_control' => 'nullable|string|max:255',
            'mqtt_topic_status' => 'nullable|string|max:255',
            'esp_device_id' => 'nullable|string|max:255',
            'lock_relay_pin' => 'nullable|integer|min:0|max:40',
            'status' => 'boolean',
            'config' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        $automation = Automation::create($request->all());

        // Log the action
        Log::logForModel(
            $automation,
            'automation_created',
            "Automatizare nouă creată: {$automation->name}",
            'success',
            [
                'type' => $automation->type,
                'mqtt_broker' => $automation->mqtt_broker,
                'esp_device_id' => $automation->esp_device_id,
            ]
        );

        return response()->json([
            'automation' => $automation,
            'message' => 'Automatizare creată cu succes',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $automation = Automation::findOrFail($id);
        
        // Log the action
        Log::logForModel(
            $automation,
            'automation_viewed',
            "Automatizare vizualizată: {$automation->name}",
            'info'
        );
        
        return response()->json([
            'automation' => $automation,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $automation = Automation::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:pool_access,proxy,other',
            'mqtt_broker' => 'nullable|string|max:255',
            'mqtt_port' => 'nullable|integer|min:1|max:65535',
            'mqtt_username' => 'nullable|string|max:255',
            'mqtt_password' => 'nullable|string|max:255',
            'mqtt_topic_control' => 'nullable|string|max:255',
            'mqtt_topic_status' => 'nullable|string|max:255',
            'esp_device_id' => 'nullable|string|max:255',
            'lock_relay_pin' => 'nullable|integer|min:0|max:40',
            'status' => 'boolean',
            'config' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        $oldData = $automation->toArray();
        $automation->update($request->all());

        // Log the action
        Log::logForModel(
            $automation,
            'automation_updated',
            "Automatizare actualizată: {$automation->name}",
            'success',
            [
                'old_data' => $oldData,
                'new_data' => $automation->toArray(),
            ]
        );

        return response()->json([
            'automation' => $automation,
            'message' => 'Automatizare actualizată cu succes',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $automation = Automation::findOrFail($id);
        
        $automationData = [
            'id' => $automation->id,
            'name' => $automation->name,
            'type' => $automation->type,
        ];
        
        $automation->delete();

        // Log the action
        Log::logWarning(
            'automation_deleted',
            "Automatizare ștearsă: {$automationData['name']}",
            ['deleted_automation' => $automationData]
        );

        return response()->json([
            'message' => 'Automatizare ștearsă cu succes',
        ]);
    }

    /**
     * Control pool access lock
     */
    public function controlPoolAccess(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:unlock,lock,status',
        ]);

        $automation = Automation::getPoolAccess();
        
        if (!$automation) {
            throw ValidationException::withMessages([
                'automation' => ['Configurația pentru accesul la piscină nu a fost găsită.'],
            ]);
        }

        if (!$automation->status) {
            throw ValidationException::withMessages([
                'automation' => ['Automatizarea pentru piscină este dezactivată.'],
            ]);
        }

        $user = auth()->user();
        $action = $request->action;

        // Simulate MQTT communication (in real implementation, this would send MQTT messages)
        $mqttMessage = json_encode([
            'action' => $action,
            'device_id' => $automation->esp_device_id,
            'pin' => $automation->lock_relay_pin,
            'timestamp' => now()->toISOString(),
        ]);

        $espResponse = json_encode([
            'status' => 'success',
            'message' => "Zăvorul a fost " . ($action === 'unlock' ? 'deblocat' : 'blocat'),
            'timestamp' => now()->toISOString(),
        ]);

        // Log the access attempt
        $accessLog = PoolAccessLog::logAccess(
            $automation->id,
            $user->id,
            $action === 'unlock' ? 'unlock' : 'lock',
            $mqttMessage,
            $espResponse,
            'success',
            [
                'user_name' => $user->name,
                'action' => $action,
            ]
        );

        // Log the action in main logs
        Log::logForModel(
            $automation,
            'pool_access_controlled',
            "Acces la piscină controlat: {$action} de către {$user->name}",
            'success',
            [
                'user_id' => $user->id,
                'action' => $action,
                'access_log_id' => $accessLog->id,
            ]
        );

        return response()->json([
            'message' => "Zăvorul a fost " . ($action === 'unlock' ? 'deblocat' : 'blocat') . " cu succes",
            'access_log' => $accessLog,
            'mqtt_message' => $mqttMessage,
            'esp_response' => $espResponse,
        ]);
    }

    /**
     * Get pool access logs
     */
    public function getPoolAccessLogs(Request $request): JsonResponse
    {
        $query = PoolAccessLog::with(['automation', 'user'])
            ->whereHas('automation', function ($q) {
                $q->where('type', 'pool_access');
            });

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mqtt_message', 'like', "%{$search}%")
                  ->orWhere('esp_response', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->input('per_page', 50);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'logs' => $logs->items(),
            'total_pages' => $logs->lastPage(),
            'current_page' => $logs->currentPage(),
            'total' => $logs->total(),
        ]);
    }

    /**
     * Test MQTT connection
     */
    public function testMqttConnection(Request $request): JsonResponse
    {
        $request->validate([
            'automation_id' => 'required|exists:automations,id',
        ]);

        $automation = Automation::findOrFail($request->automation_id);

        // Simulate MQTT connection test
        $connectionTest = [
            'broker' => $automation->mqtt_broker,
            'port' => $automation->mqtt_port,
            'status' => 'connected', // In real implementation, this would test actual connection
            'timestamp' => now()->toISOString(),
        ];

        // Log the test
        Log::logForModel(
            $automation,
            'mqtt_connection_tested',
            "Test conexiune MQTT pentru {$automation->name}",
            'info',
            $connectionTest
        );

        return response()->json([
            'message' => 'Test conexiune MQTT completat',
            'connection_test' => $connectionTest,
        ]);
    }

    /**
     * Get automation statistics
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_automations' => Automation::count(),
            'active_automations' => Automation::where('status', true)->count(),
            'pool_access_automations' => Automation::where('type', 'pool_access')->count(),
            'proxy_automations' => Automation::where('type', 'proxy')->count(),
            'total_access_logs' => PoolAccessLog::count(),
            'today_access_logs' => PoolAccessLog::whereDate('created_at', today())->count(),
            'successful_access' => PoolAccessLog::where('status', 'success')->count(),
            'failed_access' => PoolAccessLog::where('status', 'failed')->count(),
        ];

        return response()->json($stats);
    }
}
