<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->isSysAdmin()) {
            return response()->json(['message' => 'Acces interzis'], 403);
        }

        $tenants = Tenant::withCount(['users', 'automations'])
            ->orderBy('name')
            ->get();

        Log::logInfo(
            'tenants_listed',
            'Lista de tenant-uri a fost vizualizată',
            ['count' => $tenants->count()]
        );

        return response()->json([
            'tenants' => $tenants,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->isSysAdmin()) {
            return response()->json(['message' => 'Acces interzis'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'subscription_plan' => 'required|in:basic,premium,enterprise',
            'subscription_expires_at' => 'nullable|date',
            'max_users' => 'required|integer|min:1|max:1000',
            'max_automations' => 'required|integer|min:1|max:100',
            'features' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $tenant = Tenant::create($request->all());

        // Creez rolurile implicite pentru tenant
        $this->createDefaultRoles($tenant);

        Log::logForModel(
            $tenant,
            'tenant_created',
            "Tenant nou creat: {$tenant->name}",
            'success',
            [
                'subscription_plan' => $tenant->subscription_plan,
                'max_users' => $tenant->max_users,
                'max_automations' => $tenant->max_automations,
            ]
        );

        return response()->json([
            'tenant' => $tenant,
            'message' => 'Tenant creat cu succes',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::withCount(['users', 'automations'])->findOrFail($id);
        
        if (!$user->canAccessTenant($tenant->id)) {
            return response()->json(['message' => 'Acces interzis'], 403);
        }

        Log::logForModel(
            $tenant,
            'tenant_viewed',
            "Tenant vizualizat: {$tenant->name}",
            'info'
        );

        return response()->json([
            'tenant' => $tenant,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::findOrFail($id);
        
        if (!$user->isSysAdmin()) {
            return response()->json(['message' => 'Acces interzis'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tenants,slug,' . $id,
            'domain' => 'nullable|string|max:255|unique:tenants,domain,' . $id,
            'description' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'status' => 'required|in:active,inactive,suspended',
            'subscription_plan' => 'required|in:basic,premium,enterprise',
            'subscription_expires_at' => 'nullable|date',
            'max_users' => 'required|integer|min:1|max:1000',
            'max_automations' => 'required|integer|min:1|max:100',
            'features' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $oldData = $tenant->toArray();
        $tenant->update($request->all());

        Log::logForModel(
            $tenant,
            'tenant_updated',
            "Tenant actualizat: {$tenant->name}",
            'success',
            [
                'old_data' => $oldData,
                'new_data' => $tenant->toArray(),
            ]
        );

        return response()->json([
            'tenant' => $tenant,
            'message' => 'Tenant actualizat cu succes',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::findOrFail($id);
        
        if (!$user->isSysAdmin()) {
            return response()->json(['message' => 'Acces interzis'], 403);
        }

        if ($tenant->users()->count() > 0) {
            throw ValidationException::withMessages([
                'tenant' => ['Nu se poate șterge tenant-ul deoarece are utilizatori asociați.'],
            ]);
        }

        $tenantData = [
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
        ];

        $tenant->delete();

        Log::logWarning(
            'tenant_deleted',
            "Tenant șters: {$tenantData['name']}",
            ['deleted_tenant' => $tenantData]
        );

        return response()->json([
            'message' => 'Tenant șters cu succes',
        ]);
    }

    /**
     * Get current tenant information
     */
    public function current(): JsonResponse
    {
        $user = auth()->user();
        
        if ($user->isSysAdmin()) {
            return response()->json([
                'tenant' => null,
                'is_sysadmin' => true,
            ]);
        }

        $tenant = $user->tenant;
        
        if (!$tenant) {
            return response()->json([
                'tenant' => null,
                'is_sysadmin' => false,
            ]);
        }

        return response()->json([
            'tenant' => $tenant,
            'is_sysadmin' => false,
        ]);
    }

    /**
     * Get tenant statistics
     */
    public function statistics(string $id): JsonResponse
    {
        $user = auth()->user();
        $tenant = Tenant::findOrFail($id);
        
        if (!$user->canAccessTenant($tenant->id)) {
            return response()->json(['message' => 'Acces interzis'], 403);
        }

        $stats = [
            'total_users' => $tenant->users()->count(),
            'active_users' => $tenant->users()->where('status', 'active')->count(),
            'total_automations' => $tenant->automations()->count(),
            'active_automations' => $tenant->automations()->where('status', true)->count(),
            'total_logs' => $tenant->logs()->count(),
            'today_logs' => $tenant->logs()->whereDate('created_at', today())->count(),
            'total_access_logs' => $tenant->poolAccessLogs()->count(),
            'today_access_logs' => $tenant->poolAccessLogs()->whereDate('created_at', today())->count(),
            'subscription_status' => $tenant->isSubscriptionExpired() ? 'expired' : 'active',
            'can_add_user' => $tenant->canAddUser(),
            'can_add_automation' => $tenant->canAddAutomation(),
        ];

        return response()->json($stats);
    }

    /**
     * Create default roles for a tenant
     */
    private function createDefaultRoles(Tenant $tenant): void
    {
        $defaultRoles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Administrator cu acces complet',
                'permissions' => [
                    'users.manage',
                    'roles.manage',
                    'automations.manage',
                    'logs.view',
                    'settings.manage',
                ],
            ],
            [
                'name' => 'tenantadmin',
                'display_name' => 'Tenant Administrator',
                'description' => 'Administrator de tenant cu acces la gestionarea utilizatorilor și configurare',
                'permissions' => [
                    'users.manage',
                    'automations.configure',
                    'logs.view',
                    'settings.view',
                ],
            ],
            [
                'name' => 'cex',
                'display_name' => 'CEX',
                'description' => 'Manager cu acces la control și monitorizare',
                'permissions' => [
                    'automations.control',
                    'logs.view',
                    'reports.view',
                ],
            ],
            [
                'name' => 'tehnic',
                'display_name' => 'Tehnic',
                'description' => 'Tehnician cu acces la configurare și mentenanță',
                'permissions' => [
                    'automations.configure',
                    'logs.view',
                    'maintenance.access',
                ],
            ],
            [
                'name' => 'user',
                'display_name' => 'Utilizator',
                'description' => 'Utilizator standard',
                'permissions' => [
                    'dashboard.view',
                ],
            ],
        ];

        foreach ($defaultRoles as $roleData) {
            Role::create([
                'tenant_id' => $tenant->id,
                'name' => $roleData['name'],
                'display_name' => $roleData['display_name'],
                'description' => $roleData['description'],
                'permissions' => $roleData['permissions'],
                'is_active' => true,
            ]);
        }
    }

    /**
     * Switch tenant context (for sysadmin)
     */
    public function switchTenant(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->isSysAdmin()) {
            return response()->json(['message' => 'Acces interzis'], 403);
        }

        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $tenant = Tenant::findOrFail($request->tenant_id);
        
        if (!$tenant->isActive()) {
            return response()->json(['message' => 'Tenant-ul nu este activ'], 400);
        }

        // Setăm tenant-ul în sesiune pentru sysadmin
        session(['current_tenant_id' => $tenant->id]);

        Log::logInfo(
            'tenant_switched',
            "Sysadmin a schimbat contextul la tenant: {$tenant->name}",
            ['tenant_id' => $tenant->id]
        );

        return response()->json([
            'message' => 'Context schimbat cu succes',
            'tenant' => $tenant,
        ]);
    }

    /**
     * Get all tenants for sysadmin
     */
    public function allTenants(): JsonResponse
    {
        $user = auth()->user();
        
        if (!$user->isSysAdmin()) {
            return response()->json(['message' => 'Acces interzis'], 403);
        }

        $tenants = Tenant::withCount(['users', 'automations'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'tenants' => $tenants,
        ]);
    }
}
