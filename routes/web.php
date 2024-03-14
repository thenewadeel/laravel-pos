<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::resources([
        'products'   => ProductController::class,
        'customers'   => CustomerController::class,
        'orders'      => OrderController::class,
        'users'       => UsersController::class,
        'shops'       => ShopController::class,
        'reports'     => ReportsController::class
    ]);

    Route::get('/listOfProducts', [ProductController::class, 'listOf']);
    Route::get('/listOfCustomers', [CustomerController::class, 'listOf']);
    Route::get('/listOfOrders', [OrderController::class, 'listOf']);
    Route::get('/listOfUsers', [UsersController::class, 'listOf']);
    Route::get('/listOfShops', [ShopController::class, 'listOf']);
    Route::get('/listOfReports', [ReportsController::class, 'listOf']);

    Route::get('/shops/{shop}/export', [ShopController::class, 'exportReport'])->name('shops.export');

    Route::namespace('Admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

        Route::get('/export', [UsersController::class, 'export']);
        Route::get('/download', [UsersController::class, 'downloadinExcel']);

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
        Route::delete('/cart/delete', [CartController::class, 'delete']);
        Route::delete('/cart/empty', [CartController::class, 'empty']);

        // Transaltions route for React component
        Route::get('/locale/{type}', function ($type) {
            $translations = trans($type);
            return response()->json($translations);
        });
    });
});
