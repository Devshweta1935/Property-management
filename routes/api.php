<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\QueueHealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Property routes
    Route::apiResource('properties', PropertyController::class);
    Route::get('/my-properties', [PropertyController::class, 'myProperties']);
    Route::post('/test-email', [PropertyController::class, 'testEmail']);
    
    // Queue health monitoring routes
    Route::get('/queue/health', [QueueHealthController::class, 'health']);
    Route::get('/queue/stats', [QueueHealthController::class, 'stats']);
});
