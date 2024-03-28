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
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\InventoryController;
use App\Models\Category;
use App\Models\InventoryItem;
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
        'reports'     => ReportsController::class,
        'categories'  => CategoryController::class,
        'expenses'    => ExpenseController::class,
        'inventoryItems' => InventoryItemController::class
    ]);

    Route::get('/listOfProducts', [ProductController::class, 'listOf']);
    Route::get('/listOfCustomers', [CustomerController::class, 'listOf']);
    Route::get('/listOfOrders', [OrderController::class, 'listOf']);
    Route::get('/listOfUsers', [UsersController::class, 'listOf']);
    Route::get('/listOfShops', [ShopController::class, 'listOf']);
    Route::get('/listOfReports', [ReportsController::class, 'listOf']);
    Route::get('/listOfCategories', [CategoryController::class, 'listOf']);
    Route::get('/listOfinventoryItems', [InventoryItemController::class, 'listOf']);

    Route::post('/orders/{order}/addPayment', [OrderController::class, 'addPayment'])->name('orders.payments.store');

    Route::put('/orders/{order}/discounts', [OrderController::class, 'updateDiscounts'])
        ->name('orders.discounts.update');
    Route::delete('/orders/{order}/payments/{payment}', [OrderController::class, 'destroyPayment'])
        ->name('orders.payments.destroy');

    Route::post('/orders/{order}/addItem', [OrderController::class, 'addItem'])->name('order.items.store');
    Route::delete('/orders/{order}/item/{item}', [OrderController::class, 'destroyItem'])
        ->name('order.items.destroy');
    Route::get('/orders/print/{order}', [OrderController::class, 'printPdf'])->name('orders.print');
    Route::get('/orders/printPreview/{order}', [OrderController::class, 'printPreview'])->name('orders.print.preview');
    Route::get('/shops/{shop}/export', [ShopController::class, 'exportReport'])->name('shops.export');

    Route::namespace('Stockboy')->prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])
            ->name('inventory.index');
        Route::get('/evaluation', [InventoryController::class, 'evaluation'])->name('inventory.evaluation');
        Route::get('/issue', [InventoryController::class, 'issue'])->name('inventory.issue');
        Route::get('/deposit', [InventoryController::class, 'deposit'])->name('inventory.deposit');
        Route::get('/stockTaking', [InventoryController::class, 'stockTaking'])->name('inventory.stockTaking');
    });

    Route::namespace('Accountant')->group(function () {
        Route::get('/dailySale', [ReportsController::class, 'dailySale'])->name('reports.dailySale');
    });

    Route::namespace('Admin')->group(function () {

        Route::post('/catprod', [CategoryController::class, 'catprodstore'])->name('categories.products.store');
        Route::delete('/catprod/{category_id}/{product_id}', [CategoryController::class, 'catproddelete'])->name('categories.products.delete');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

        Route::get('/export', [UsersController::class, 'export']);
        Route::get('/download', [UsersController::class, 'downloadinExcel']);

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
        Route::delete('/cart/delete', [CartController::class, 'delete']);
        Route::delete('/cart/empty', [CartController::class, 'empty']);

        Route::post('/users/{user}/updateShops', [UsersController::class, 'updateShops'])
            ->name('users.updateShops');
        Route::post('/shops/{shop}/updateCategories', [ShopController::class, 'updateCategories'])
            ->name('shop.updateCategories');

        // Transaltions route for React component
        Route::get('/locale/{type}', function ($type) {
            $translations = trans($type);
            return response()->json($translations);
        });
    });
});
