<?php

use App\Http\Controllers\API\V1\FloorController;
use App\Http\Controllers\API\V1\FloorSyncController;
use App\Http\Controllers\API\V1\OrderController;
use App\Http\Controllers\API\V1\SyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Debug route to check authentication
Route::get('/debug-auth', function (Request $request) {
    return [
        'authenticated' => auth()->check(),
        'user_id' => auth()->id(),
        'guard' => config('auth.defaults.guard'),
        'session' => $request->hasSession(),
        'cookies' => $request->cookies->all(),
    ];
});

// API V1 Routes - using 'auth:sanctum' middleware with session authentication
// EnsureFrontendRequestsAreStateful middleware allows Sanctum to use session cookies
Route::prefix('v1')->middleware(['auth:sanctum', \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])->group(function () {
    
    // Order API Endpoints
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::put('/orders/{id}', [OrderController::class, 'update']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);
    
    // Order Items API Endpoints
    Route::get('/orders/{id}/items', [OrderController::class, 'getItems']);
    Route::post('/orders/{id}/items', [OrderController::class, 'addItem']);
    Route::put('/orders/{id}/items/{itemId}', [OrderController::class, 'updateItem']);
    Route::delete('/orders/{id}/items/{itemId}', [OrderController::class, 'deleteItem']);
    
    // Order Payment API Endpoints
    Route::post('/orders/{id}/payment', [OrderController::class, 'processPayment']);
    Route::post('/orders/{id}/close', [OrderController::class, 'closeOrder']);
    
    // Floor Management API Endpoints
    Route::get('/floors', [FloorController::class, 'index']);
    Route::post('/floors', [FloorController::class, 'store']);
    Route::put('/floors/{id}', [FloorController::class, 'update']);
    Route::delete('/floors/{id}', [FloorController::class, 'destroy']);
    Route::post('/floors/{id}/tables', [FloorController::class, 'storeTable']);
    Route::put('/tables/{id}', [FloorController::class, 'updateTable']);
    Route::delete('/tables/{id}', [FloorController::class, 'destroyTable']);
    Route::patch('/tables/{id}/status', [FloorController::class, 'updateTableStatus']);
    
    // Offline Sync API Endpoints
    Route::post('/sync/upload', [SyncController::class, 'upload']);
    Route::get('/sync/status', [SyncController::class, 'status']);
    Route::post('/sync/download', [SyncController::class, 'download']);
    Route::post('/sync/acknowledge', [SyncController::class, 'acknowledge']);
    Route::post('/sync/conflict', [SyncController::class, 'reportConflict']);
    Route::get('/sync/conflicts', [SyncController::class, 'listConflicts']);
    Route::put('/sync/conflicts/{id}', [SyncController::class, 'resolveConflict']);
    Route::delete('/sync/conflicts/{id}', [SyncController::class, 'dismissConflict']);
    
    // Floor Management Sync API
    Route::get('/sync/floors', [FloorSyncController::class, 'downloadFloors']);
    Route::post('/sync/tables/upload', [FloorSyncController::class, 'uploadAssignments']);
    Route::get('/sync/floors/status', [FloorSyncController::class, 'getSyncStatus']);
    Route::get('/sync/tables/download', [FloorSyncController::class, 'downloadUpdates']);
    Route::post('/sync/floors/acknowledge', [FloorSyncController::class, 'acknowledge']);
});
