<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Responses\ErrorResponse;

class MeController extends Controller
{

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function readProfile(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Load roles, tenant, and permissions
        $user->load('roles', 'tenant');

        // Get all permissions (direct + inherited from roles)
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        // Returnez user-ul cu roles, tenant și permissions ca JSON:API simplificat
        return response()->json([
            'data' => [
                'type' => 'users',
                'id' => (string)$user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'apartment' => $user->apartment,
                'staircase' => $user->staircase,
                'floor' => $user->floor,
                'profile_image' => $user->profile_image,
                'tenant_id' => $user->tenant_id,
                'tenant' => $user->tenant ? [
                    'id' => $user->tenant->id,
                    'name' => $user->tenant->name,
                    'address' => $user->tenant->address,
                    'fiscal_code' => $user->tenant->fiscal_code,
                ] : null,
                'roles' => $user->roles->map(function($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                })->toArray(),
                'permissions' => $permissions,
            ],
        ]);
    }

     /**
     * Update the specified resource.
     * Not named update because it conflicts with JsonApiController update method signature
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $http = new Client(['verify' => false]);

        $headers = $this->parseHeaders($request->header());

        $input = $request->json()->all();

        $input['data']['id'] = (string)auth()->id();
        $input['data']['type'] = 'users';

        $data = [
            'headers' => $headers,
            'json' => $input,
            'query' => $request->query()
        ];
       
        try { 
            $response = $http->patch(route('v2.users.update', ['user' => auth()->id()]), $data);
         
        } catch (ClientException $e) {
            $errors = json_decode($e->getResponse()->getBody()->getContents(), true)['errors'];
            $errors = collect($errors)->map(function ($error) {
                return Error::fromArray($error);
            });
            return ErrorResponse::make($errors);
        }
        $responseBody = json_decode((string)$response->getBody(), true);
        $responseStatus = $response->getStatusCode();
        $responseHeaders = $this->parseHeaders($response->getHeaders());

        unset($responseHeaders['Transfer-Encoding']);

        return response()->json($responseBody, $responseStatus)->withHeaders($responseHeaders);
    }

    /**
     * Change user password
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validare eșuată',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Parola curentă este incorectă'
            ], 400);
        }

        // Nu folosim Hash::make() aici pentru că modelul User
        // are un Attribute mutator care face hash automat
        \Log::info('Changing password', [
            'user_id' => $user->id,
            'old_hash' => $user->password,
            'new_password_length' => strlen($request->new_password)
        ]);

        $user->password = $request->new_password;
        $user->save();
        
        // Revoke all tokens pentru a forța re-autentificarea
        $user->tokens()->delete();

        \Log::info('Password changed successfully', [
            'user_id' => $user->id,
            'saved_hash' => $user->fresh()->password
        ]);

        return response()->json([
            'message' => 'Parola a fost actualizată cu succes'
        ], 200);
    }

      /**
     * Parse headers to collapse internal arrays
     * TODO: move to helpers
     *
     * @param array $headers
     * @return array
     */
    protected function parseHeaders($headers)
    {
        return collect($headers)->map(function ($item) {
            return $item[0];
        })->toArray();
    }

}
