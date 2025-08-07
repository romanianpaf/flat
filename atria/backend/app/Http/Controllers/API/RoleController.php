<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        
        // Sysadmin poate vedea toate rolurile
        if ($user->role === 'sysadmin') {
            $roles = Role::orderBy('display_name')->get();
        } else {
            // Alți utilizatori văd doar rolurile din tenant-ul lor
            $roles = Role::where('tenant_id', $user->tenant_id)
                ->orderBy('display_name')
                ->get();
        }
        
        // Log the action
        Log::logInfo(
            'roles_listed',
            'Lista de roluri a fost vizualizată',
            ['count' => $roles->count(), 'tenant_id' => $user->tenant_id]
        );
        
        return response()->json([
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $roleData = $request->all();
        
        // Setează tenant_id pentru roluri noi (doar sysadmin poate crea roluri fără tenant)
        if ($user->role !== 'sysadmin') {
            $roleData['tenant_id'] = $user->tenant_id;
        }

        $role = Role::create($roleData);

        // Log the action
        Log::logSuccess(
            'role_created',
            "Rol nou creat: {$role->display_name}",
            [
                'role_id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'is_active' => $role->is_active,
                'tenant_id' => $role->tenant_id
            ]
        );

        return response()->json([
            'role' => $role,
            'message' => 'Rol creat cu succes',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        
        // Log the action
        Log::logInfo(
            'role_viewed',
            "Rol vizualizat: {$role->display_name}",
            ['role_id' => $role->id]
        );
        
        return response()->json([
            'role' => $role,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $oldData = [
            'name' => $role->name,
            'display_name' => $role->display_name,
            'description' => $role->description,
            'is_active' => $role->is_active
        ];

        $role->update($request->all());

        // Log the action
        Log::logSuccess(
            'role_updated',
            "Rol actualizat: {$role->display_name}",
            [
                'role_id' => $role->id,
                'old_data' => $oldData,
                'new_data' => [
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'is_active' => $role->is_active
                ]
            ]
        );

        return response()->json([
            'role' => $role,
            'message' => 'Rol actualizat cu succes',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        
        // Verifică dacă rolul este folosit de utilizatori
        if ($role->users()->count() > 0) {
            Log::logWarning(
                'role_deletion_attempted',
                "Încercare de ștergere a rolului {$role->display_name} care este atribuit utilizatorilor",
                [
                    'role_id' => $role->id,
                    'users_count' => $role->users()->count()
                ]
            );
            
            throw ValidationException::withMessages([
                'role' => ['Nu se poate șterge rolul deoarece este atribuit utilizatorilor.'],
            ]);
        }
        
        $roleData = [
            'id' => $role->id,
            'name' => $role->name,
            'display_name' => $role->display_name
        ];
        
        $role->delete();

        // Log the action
        Log::logWarning(
            'role_deleted',
            "Rol șters: {$roleData['display_name']}",
            ['deleted_role' => $roleData]
        );

        return response()->json([
            'message' => 'Rol șters cu succes',
        ]);
    }
}
