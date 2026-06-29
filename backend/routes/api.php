<?php

use App\Http\Controllers\Api\V2\Auth\LoginController;
use App\Http\Controllers\Api\V2\Auth\LogoutController;
use App\Http\Controllers\Api\V2\Auth\RegisterController;
use App\Http\Controllers\Api\V2\MeController;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;
use App\Http\Controllers\Api\V2\Auth\ForgotPasswordController;
use App\Http\Controllers\Api\V2\Auth\ResetPasswordController;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v2')->middleware('json.api')->group(function () {
    Route::post('/login', LoginController::class)->name('login');
    Route::post('/register', RegisterController::class);
    Route::post('/logout', LogoutController::class)->middleware('auth:api');
    Route::post('/password-forgot', ForgotPasswordController::class);
    Route::post('/password-reset', ResetPasswordController::class)->name('password.reset');
    
    // Weather API (public, cu cache backend)
    Route::get('weather/current', [App\Http\Controllers\Api\V2\WeatherController::class, 'getCurrentWeather']);

    // Registration requests (public endpoints)
    Route::get('tenants/public', [App\Http\Controllers\Api\V2\RegistrationRequestController::class, 'publicTenants']);
    Route::get('tenants/{tenant}/apartments/public', [App\Http\Controllers\Api\V2\RegistrationRequestController::class, 'publicApartments']);
    Route::post('registration-requests', [App\Http\Controllers\Api\V2\RegistrationRequestController::class, 'store'])
        ->middleware('throttle:10,1'); // Rate limit: 10 requests per minute

    // Carte de imobil (custom REST, JSON simplu)
    Route::middleware(['auth:api', 'throttle:api'])->group(function () {
        Route::get('my-apartments', [App\Http\Controllers\Api\V2\CarteiImobil\MyApartmentsController::class, 'index']);
        Route::get('occupants/submissions', [App\Http\Controllers\Api\V2\CarteiImobil\OccupantSubmissionsController::class, 'index']);

        // Config imobil (admin/cex/sysadmin)
        Route::get('tenant-building', [App\Http\Controllers\Api\V2\CarteiImobil\TenantBuildingController::class, 'show']);
        Route::get('tenant-building/tenants', [App\Http\Controllers\Api\V2\CarteiImobil\TenantBuildingController::class, 'tenants']);
        Route::get('tenant-building/options', [App\Http\Controllers\Api\V2\CarteiImobil\TenantBuildingController::class, 'options']);
        Route::get('tenant-building/apartments', [App\Http\Controllers\Api\V2\CarteiImobil\TenantBuildingController::class, 'apartmentsList']);
        Route::post('tenant-building/staircases', [App\Http\Controllers\Api\V2\CarteiImobil\TenantBuildingController::class, 'storeStaircase']);
        Route::put('tenant-building/staircases/{staircase}', [App\Http\Controllers\Api\V2\CarteiImobil\TenantBuildingController::class, 'updateStaircase']);
        Route::delete('tenant-building/staircases/{staircase}', [App\Http\Controllers\Api\V2\CarteiImobil\TenantBuildingController::class, 'destroyStaircase']);
        Route::post('tenant-building/staircases/{staircase}/apartments/sync', [App\Http\Controllers\Api\V2\CarteiImobil\TenantBuildingController::class, 'syncApartments']);
        Route::put('tenant-building/apartments/{apartment}/floor', [App\Http\Controllers\Api\V2\CarteiImobil\TenantBuildingController::class, 'updateApartmentFloor']);

        Route::get('apartments/{apartment}/occupants', [App\Http\Controllers\Api\V2\CarteiImobil\ApartmentOccupantsController::class, 'index']);
        Route::post('apartments/{apartment}/occupants', [App\Http\Controllers\Api\V2\CarteiImobil\ApartmentOccupantsController::class, 'store']);
        Route::post('apartments/{apartment}/occupants/submit', [App\Http\Controllers\Api\V2\CarteiImobil\ApartmentOccupantsController::class, 'submit']);
        Route::post('apartments/{apartment}/occupants/approve', [App\Http\Controllers\Api\V2\CarteiImobil\ApartmentOccupantsController::class, 'approve']);
        Route::post('apartments/{apartment}/occupants/reject', [App\Http\Controllers\Api\V2\CarteiImobil\ApartmentOccupantsController::class, 'reject']);
        Route::get('apartments/{apartment}/occupants/export.pdf', [App\Http\Controllers\Api\V2\CarteiImobil\ApartmentOccupantsController::class, 'exportPdf']);

        Route::put('occupants/{occupant}', [App\Http\Controllers\Api\V2\CarteiImobil\OccupantController::class, 'update']);
        Route::delete('occupants/{occupant}', [App\Http\Controllers\Api\V2\CarteiImobil\OccupantController::class, 'destroy']);

        // Impersonare (sysadmin) — "login ca user"
        Route::get('impersonate/candidates', [App\Http\Controllers\Api\V2\ImpersonationController::class, 'candidates']);
        Route::post('impersonate-leave', [App\Http\Controllers\Api\V2\ImpersonationController::class, 'stop']);
        Route::post('impersonate/{user}', [App\Http\Controllers\Api\V2\ImpersonationController::class, 'start']);

        // Registration requests management (admin/cex)
        Route::get('registration-requests', [App\Http\Controllers\Api\V2\RegistrationRequestController::class, 'index']);
        Route::get('registration-requests/{registrationRequest}', [App\Http\Controllers\Api\V2\RegistrationRequestController::class, 'show']);
        Route::get('registration-requests/{registrationRequest}/document', [App\Http\Controllers\Api\V2\RegistrationRequestController::class, 'downloadDocument']);
        Route::post('registration-requests/{registrationRequest}/approve', [App\Http\Controllers\Api\V2\RegistrationRequestController::class, 'approve']);
        Route::post('registration-requests/{registrationRequest}/reject', [App\Http\Controllers\Api\V2\RegistrationRequestController::class, 'reject']);

    });
});

// MQTT Test endpoints
Route::prefix('v2')->middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::get('mqtt-test/status', [App\Http\Controllers\Api\MqttTestController::class, 'getTestStatus']);
    Route::post('mqtt-test/send', [App\Http\Controllers\Api\MqttTestController::class, 'sendTest']);
});

// MQTT Test Connection (for tenant configuration)
Route::prefix('v2')->middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::post('tenants/{tenant}/test-mqtt', [App\Http\Controllers\Api\MqttTestController::class, 'testTenantConnection']);
});

JsonApiRoute::server('v2')->prefix('v2')->resources(function (ResourceRegistrar $server) {
    $server->resource('categories', JsonApiController::class);
    $server->resource('items', JsonApiController::class);
    $server->resource('automations', JsonApiController::class);
    $server->resource('permissions', JsonApiController::class)->only('index');
    $server->resource('polls', JsonApiController::class);
    $server->resource('poll-options', JsonApiController::class);
    $server->resource('roles', JsonApiController::class);
    $server->resource('service-categories', JsonApiController::class);
    $server->resource('service-subcategories', JsonApiController::class);
    $server->resource('service-providers', JsonApiController::class);
    $server->resource('service-provider-ratings', JsonApiController::class);
    $server->resource('tags', JsonApiController::class);
    $server->resource('tenants', JsonApiController::class);
    $server->resource('user-voices', JsonApiController::class);
    $server->resource('users', JsonApiController::class);
    Route::get('me', [MeController::class, 'readProfile']);
    Route::patch('me', [MeController::class, 'updateProfile']);
    Route::post('change-password', [MeController::class, 'changePassword']);
    Route::post('user-voices/{userVoice}/vote', [App\Http\Controllers\Api\V2\UserVoiceController::class, 'vote']);
    Route::post('poll-options/{pollOption}/vote', [App\Http\Controllers\Api\V2\PollOptionController::class, 'vote']);
    Route::post('/uploads/{resource}/{id}/{field}', UploadController::class);
    
    // Weather cache management (authenticated)
    Route::post('weather/clear-cache', [App\Http\Controllers\Api\V2\WeatherController::class, 'clearCache']);
    
    // Role permissions management
    Route::post('roles/{role}/sync-permissions', [App\Http\Controllers\Api\V2\RoleController::class, 'syncPermissions']);
    Route::get('available-permissions', [App\Http\Controllers\Api\V2\RoleController::class, 'availablePermissions']);
});
