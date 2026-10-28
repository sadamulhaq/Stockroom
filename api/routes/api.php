<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\SupplierController;
use Illuminate\Support\Facades\Route;

// Auth routes (public, rate-limited)
Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->withoutMiddleware('throttle:10,1')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Dashboard — all roles can view
    Route::get('dashboard', [DashboardController::class, 'index']);

    // Categories — all can read, admin/manager can write
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    Route::middleware('role:admin,manager')->group(function () {
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{category}', [CategoryController::class, 'update']);
        Route::patch('categories/{category}', [CategoryController::class, 'update']);
        Route::delete('categories/{category}', [CategoryController::class, 'destroy']);
    });

    // Suppliers — all can read, admin/manager can write
    Route::get('suppliers', [SupplierController::class, 'index']);
    Route::get('suppliers/{supplier}', [SupplierController::class, 'show']);
    Route::middleware('role:admin,manager')->group(function () {
        Route::post('suppliers', [SupplierController::class, 'store']);
        Route::put('suppliers/{supplier}', [SupplierController::class, 'update']);
        Route::patch('suppliers/{supplier}', [SupplierController::class, 'update']);
        Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy']);
    });

    // Products — all can read, admin/manager can write, admin/manager can adjust stock
    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::middleware('role:admin,manager')->group(function () {
        Route::post('products', [ProductController::class, 'store']);
        Route::put('products/{product}', [ProductController::class, 'update']);
        Route::patch('products/{product}', [ProductController::class, 'update']);
        Route::delete('products/{product}', [ProductController::class, 'destroy']);
        Route::post('products/{product}/adjust-stock', [ProductController::class, 'adjustStock']);
    });

    // Purchase Orders — all can read/create, admin/manager can update status, admin can delete
    Route::get('purchase-orders', [PurchaseOrderController::class, 'index']);
    Route::get('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'show']);
    Route::post('purchase-orders', [PurchaseOrderController::class, 'store']);
    Route::middleware('role:admin,manager')->group(function () {
        Route::put('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'update']);
        Route::patch('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'update']);
        Route::match(['put', 'patch'], 'purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus']);
    });
    Route::delete('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])
        ->middleware('role:admin');

    // Stock Movements — all can view
    Route::get('stock-movements', [StockMovementController::class, 'index']);
    Route::get('stock-movements/product/{productId}', [StockMovementController::class, 'byProduct']);
    Route::get('products/{productId}/movements', [StockMovementController::class, 'byProduct']);

    // Reports — admin/manager only
    Route::prefix('reports')->middleware('role:admin,manager')->group(function () {
        Route::get('/', [ReportController::class, 'combined']);
        Route::get('/inventory-summary', [ReportController::class, 'inventorySummary']);
        Route::get('/stock-movements', [ReportController::class, 'stockMovements']);
        Route::get('/top-products', [ReportController::class, 'topProducts']);
        Route::get('/purchase-orders', [ReportController::class, 'purchaseOrders']);
    });
});
