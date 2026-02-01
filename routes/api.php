<?php

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// API V1 Routes
Route::prefix('v1')->middleware('auth')->group(function () {
    
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
    Route::post('/sync/acknowledge', [FloorSyncController::class, 'acknowledge']);
});
