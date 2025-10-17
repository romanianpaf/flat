<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Sincronizează permisiunile unui rol
     * 
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        // Verifică permisiunea
        if (!auth()->user()->can('edit roles')) {
            return response()->json([
                'message' => 'Nu ai permisiunea să editezi roluri'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validare eșuată',
                'errors' => $validator->errors()
            ], 400);
        }

        // Sincronizează permisiunile
        $role->syncPermissions($request->permissions);

        // Reload permissions relationship
        $role->load('permissions');

        return response()->json([
            'message' => 'Permisiunile au fost actualizate cu succes',
            'data' => [
                'type' => 'roles',
                'id' => (string)$role->id,
                'attributes' => [
                    'name' => $role->name,
                ],
                'relationships' => [
                    'permissions' => [
                        'data' => $role->permissions->map(function ($permission) {
                            return [
                                'type' => 'permissions',
                                'id' => (string)$permission->id,
                                'name' => $permission->name,
                            ];
                        })->toArray(),
                    ],
                ],
            ],
        ], 200);
    }

    /**
     * Obține toate permisiunile disponibile
     * 
     * @return JsonResponse
     */
    public function availablePermissions(): JsonResponse
    {
        $permissions = \Spatie\Permission\Models\Permission::all();

        return response()->json([
            'data' => $permissions->map(function ($permission) {
                return [
                    'type' => 'permissions',
                    'id' => (string)$permission->id,
                    'name' => $permission->name,
                    'display_name' => ucwords(str_replace('_', ' ', $permission->name)),
                ];
            })->toArray(),
        ]);
    }
}

