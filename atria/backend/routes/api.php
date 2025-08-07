<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\API\AutomationController;
use App\Http\Controllers\API\TenantController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

// Protected routes
Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/admin/info', [AdminController::class, 'info']);
    
    // Tenant management (sysadmin only)
    Route::prefix('tenants')->group(function () {
        Route::get('/', [TenantController::class, 'index']);
        Route::post('/', [TenantController::class, 'store']);
        Route::get('/all', [TenantController::class, 'allTenants']);
        Route::get('/current', [TenantController::class, 'current']);
        Route::get('/{id}', [TenantController::class, 'show']);
        Route::put('/{id}', [TenantController::class, 'update']);
        Route::delete('/{id}', [TenantController::class, 'destroy']);
        Route::get('/{id}/statistics', [TenantController::class, 'statistics']);
        Route::post('/switch', [TenantController::class, 'switchTenant']);
    });
    
    // Role management
    Route::apiResource('roles', RoleController::class);
    
    // User management
    Route::apiResource('users', UserController::class);
    
    // Automation management - specific routes first
    Route::post('/automations/pool-access/control', [AutomationController::class, 'controlPoolAccess']);
    Route::get('/automations/pool-access/logs', [AutomationController::class, 'getPoolAccessLogs']);
    Route::post('/automations/mqtt/test', [AutomationController::class, 'testMqttConnection']);
    Route::get('/automations/statistics', [AutomationController::class, 'getStatistics']);
    Route::apiResource('automations', AutomationController::class);
    
    // Log management (admin only)
    Route::prefix('admin')->group(function () {
        Route::get('/logs', [LogController::class, 'index']);
        Route::get('/logs/export', [LogController::class, 'export']);
        Route::get('/logs/stats', [LogController::class, 'stats']);
        Route::delete('/logs/{id}', [LogController::class, 'destroy']);
        Route::delete('/logs', [LogController::class, 'clearOldLogs']);
    });
});
