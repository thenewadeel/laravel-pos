<?php

use App\Http\Controllers\API\V1\FloorSyncController;
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

// Floor Management Sync API
Route::prefix('v1')->middleware('auth')->group(function () {
    // Sync endpoints for offline tablets
    Route::get('/sync/floors', [FloorSyncController::class, 'downloadFloors']);
    Route::post('/sync/tables/upload', [FloorSyncController::class, 'uploadAssignments']);
    Route::get('/sync/floors/status', [FloorSyncController::class, 'getSyncStatus']);
    Route::get('/sync/tables/download', [FloorSyncController::class, 'downloadUpdates']);
    Route::post('/sync/acknowledge', [FloorSyncController::class, 'acknowledge']);
});
