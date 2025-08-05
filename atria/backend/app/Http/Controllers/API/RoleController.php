<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
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
        $roles = Role::orderBy('display_name')->get();
        
        return response()->json([
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $role = Role::create($request->all());

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

        $role->update($request->all());

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
            throw ValidationException::withMessages([
                'role' => ['Nu se poate șterge rolul deoarece este atribuit utilizatorilor.'],
            ]);
        }
        
        $role->delete();

        return response()->json([
            'message' => 'Rol șters cu succes',
        ]);
    }
}
