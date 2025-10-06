<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Servește aplicația Vue.js pentru toate rutele (SPA)
Route::get('/{any?}', function (Request $request) {
    // Obține calea completă a cererii
    $path = ltrim($request->path(), '/');
    $file = public_path($path);
    
    // Dacă există un fișier static, servește-l direct
    if ($path && file_exists($file) && is_file($file)) {
        return response()->file($file);
    }
    
    // Altfel, servește index.html pentru SPA routing
    return response()->file(public_path('index.html'));
})->where('any', '.*');
