<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    protected function success(mixed $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'errors' => null,
        ], $status);
    }

    /**
     * @param array<int,mixed>|string $errors
     */
    protected function error(array|string $errors, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'errors' => is_string($errors) ? [['message' => $errors]] : $errors,
        ], $status);
    }
}

