<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        
        // Sysadmin poate vedea toți utilizatorii
        if ($user->role === 'sysadmin') {
            $users = User::with(['roles', 'tenant'])->orderBy('name')->get();
        } else {
            // Alți utilizatori văd doar utilizatorii din tenant-ul lor
            $users = User::with(['roles', 'tenant'])
                ->where('tenant_id', $user->tenant_id)
                ->orderBy('name')
                ->get();
        }
        
        // Log the action
        Log::logInfo(
            'users_listed',
            'Lista de utilizatori a fost vizualizată',
            ['count' => $users->count(), 'tenant_id' => $user->tenant_id]
        );
        
        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_ids' => 'array',
            'role_ids.*' => 'exists:roles,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'role' => 'required|string|in:sysadmin,admin,tenantadmin,cex,tehnic,user,locatar',
            'status' => 'required|string|in:active,inactive,suspended',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $request->tenant_id,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        if ($request->has('role_ids')) {
            $user->roles()->attach($request->role_ids);
        }

        $user->load(['roles', 'tenant']);

        // Log the action
        Log::logSuccess(
            'user_created',
            "Utilizator nou creat: {$user->name}",
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'role' => $user->role,
                'role_ids' => $request->role_ids ?? []
            ]
        );

        return response()->json([
            'user' => $user,
            'message' => 'Utilizator creat cu succes',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['roles', 'tenant'])->findOrFail($id);
        
        // Log the action
        Log::logInfo(
            'user_viewed',
            "Utilizator vizualizat: {$user->name}",
            ['user_id' => $user->id]
        );
        
        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role_ids' => 'array',
            'role_ids.*' => 'exists:roles,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'role' => 'required|string|in:sysadmin,admin,tenantadmin,cex,tehnic,user,locatar',
            'status' => 'required|string|in:active,inactive,suspended',
        ]);

        $oldData = [
            'name' => $user->name,
            'email' => $user->email,
            'tenant_id' => $user->tenant_id,
            'role' => $user->role,
            'status' => $user->status,
            'roles' => $user->roles->pluck('id')->toArray()
        ];

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'tenant_id' => $request->tenant_id,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->has('role_ids')) {
            $user->roles()->sync($request->role_ids);
        }

        $user->load(['roles', 'tenant']);

        // Log the action
        Log::logSuccess(
            'user_updated',
            "Utilizator actualizat: {$user->name}",
            [
                'user_id' => $user->id,
                'old_data' => $oldData,
                'new_data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'tenant_id' => $user->tenant_id,
                    'role' => $user->role,
                    'status' => $user->status,
                    'roles' => $user->roles->pluck('id')->toArray(),
                    'password_changed' => $request->filled('password')
                ]
            ]
        );

        return response()->json([
            'user' => $user,
            'message' => 'Utilizator actualizat cu succes',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        
        // Nu permite ștergerea utilizatorului curent
        if ($user->id === auth()->id()) {
            Log::logWarning(
                'user_deletion_attempted',
                "Încercare de ștergere a propriului cont",
                ['user_id' => $user->id]
            );
            
            throw ValidationException::withMessages([
                'user' => ['Nu poți șterge propriul cont.'],
            ]);
        }
        
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ];
        
        $user->delete();

        // Log the action
        Log::logWarning(
            'user_deleted',
            "Utilizator șters: {$userData['name']}",
            ['deleted_user' => $userData]
        );

        return response()->json([
            'message' => 'Utilizator șters cu succes',
        ]);
    }
}
