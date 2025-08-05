<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::get('/admin/info', [AdminController::class, 'info']);
    
    // Role management
    Route::apiResource('roles', RoleController::class);
    
    // User management
    Route::apiResource('users', UserController::class);
});
