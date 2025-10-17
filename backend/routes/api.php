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
    Route::post('/uploads/{resource}/{id}/{field}', UploadController::class);
});
