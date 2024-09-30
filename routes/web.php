<?php

use App\Http\Controllers\{
    CartController,
    CustomerController,
    HomeController,
    OrderController,
    ProductController,
    SettingController,
    ShopController,
    ReportsController,
    UsersController,
    CategoryController,
    DiscountController,
    ExpenseController
};
// use App\Models\Category;
use AliBayat\LaravelCategorizable\Category;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/admin', [HomeController::class, 'index'])->name('admin');
    Route::resources([
        'products'   => ProductController::class,
        'customers'   => CustomerController::class,
        'orders'      => OrderController::class,
        'users'       => UsersController::class,
        'shops'       => ShopController::class,
        'reports'     => ReportsController::class,
        'categories'  => CategoryController::class,
        'expenses'    => ExpenseController::class,
        'discounts' => DiscountController::class
    ]);

    Route::get('/createNeworder', [OrderController::class, 'newEdit'])->name('createNeworder');
    Route::post('/makeNeworder', [OrderController::class, 'makeNew'])->name('makeNeworder');

    Route::get('/listOfProducts', [ProductController::class, 'listOf']);
    Route::get('/listOfCustomers', [CustomerController::class, 'listOf']);
    Route::get('/listOfOrders', [OrderController::class, 'listOf']);
    Route::get('/listOfUsers', [UsersController::class, 'listOf']);
    Route::get('/listOfShops', [ShopController::class, 'listOf']);
    Route::get('/listOfReports', [ReportsController::class, 'listOf']);
    Route::get('/listOfCategories', [CategoryController::class, 'listOf']);
    Route::get('/listOfDiscounts', [DiscountController::class, 'listOf']);

    Route::post('/orders/{order}/addPayment', [OrderController::class, 'addPayment'])->name('orders.payments.store');

    Route::put('/orders/{order}/discounts', [OrderController::class, 'updateDiscounts'])
        ->name('orders.discounts.update');
    Route::delete('/orders/{order}/payments/{payment}', [OrderController::class, 'destroyPayment'])
        ->name('orders.payments.destroy');

    Route::post('/orders/{order}/addItem', [OrderController::class, 'addItem'])->name('order.items.store');
    Route::delete('/orders/{order}/item/{item}', [OrderController::class, 'destroyItem'])
        ->name('order.items.destroy');
    Route::get('/orders/print/{order}', [OrderController::class, 'printPdf'])->name('orders.print');
    Route::get('/orders/printBulk/{orderIdsArray}', [OrderController::class, 'printBulkPdf'])->where('orders', '.*')->name('orders.printBulk');
    Route::get('/orders/printTokens/{order}', [OrderController::class, 'printTokens'])->name('orders.printTokens');
    Route::get('/orders/printPreview/{order}', [OrderController::class, 'printPreview'])->name('orders.print.preview');
    Route::get('/orders/printPOS/{order}', [OrderController::class, 'printToPOS'])->name('orders.print.POS');
    Route::get('/orders/printPOSQT/{order}', [OrderController::class, 'printToPOSQT'])->name('orders.print.QT');

    Route::get('/orders/{order}/feedback', [OrderController::class, 'getFeedback'])->name('orders.getFeedback');
    Route::post('/orders/{order}/storefeedback', [OrderController::class, 'storeFeedback'])->name('orders.storeFeedback');

    Route::get('/shops/{shop}/export', [ShopController::class, 'exportReport'])->name('shops.export');


    Route::namespace('Accountant')->group(function () {
        Route::get('/dailySale', [ReportsController::class, 'dailySale'])->name('reports.dailySale');
        Route::get('/productsReport', [ReportsController::class, 'productsReport'])->name('reports.productsReport');
        Route::get('/cashiersReport', [ReportsController::class, 'cashiersReport'])->name('reports.cashiersReport');
        Route::get('/chitsReport', [ReportsController::class, 'chitsReport'])->name('reports.chitsReport');
    });

    Route::namespace('Admin')->group(function () {

        Route::post('/catprod', [CategoryController::class, 'catprodstore'])->name('categories.products.store');
        Route::delete('/catprod/{category_id}/{product_id}', [CategoryController::class, 'catproddelete'])->name('categories.products.delete');
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

        // Bulk Excel Exports
        Route::get('/usersexport', [UsersController::class, 'export'])->name('users.export');
        Route::get('/productsexport', [ProductController::class, 'export'])->name('products.export');
        Route::get('/shopsexport', [ShopController::class, 'export'])->name('shops.export');
        Route::get('/customersexport', [CustomerController::class, 'export'])->name('customers.export');
        // Excel Imports
        Route::post('/usersimport', [UsersController::class, 'import'])->name('users.import');
        Route::post('/productsimport', [ProductController::class, 'import'])->name('products.import');
        Route::post('/shopsimport', [ShopController::class, 'import'])->name('shops.import');
        Route::post('/cutomersimport', [CustomerController::class, 'import'])->name('customers.import');

        Route::get('/download', [UsersController::class, 'downloadinExcel']);

        Route::get('/tokenShop', [OrderController::class, 'tokenShop'])->name('tokenShop');

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::post('/cart/change-qty', [CartController::class, 'changeQty']);
        Route::delete('/cart/delete', [CartController::class, 'delete']);
        Route::delete('/cart/empty', [CartController::class, 'empty']);

        Route::get('/cartTokens', [CartController::class, 'indexTokens'])->name('cart.indexTokens');


        Route::post('/users/{user}/updateShops', [UsersController::class, 'updateShops'])
            ->name('users.updateShops');
        Route::post('/shops/{shop}/updateCategories', [ShopController::class, 'updateCategories'])
            ->name('shop.updateCategories');


        // Route to get products filtered by categories
        Route::get('/productsbyCat', [ProductController::class, 'productsbyCat'])
            ->name('productsbyCat');

        // Transaltions route for React component
        Route::get('/locale/{type}', function ($type) {
            $translations = trans($type);
            return response()->json($translations);
        });

        Route::get('/activities', [ReportsController::class, 'activities'])->name('activities.index');
    });
});
