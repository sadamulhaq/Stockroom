<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();
        $totalStockValue = Product::sum(DB::raw('quantity * cost_price'));
        $lowStockCount = Product::lowStock()->where('quantity', '>', 0)->count();
        $outOfStockCount = Product::where('quantity', 0)->count();

        $recentMovements = StockMovement::with(['product', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        $recentOrders = PurchaseOrder::with(['supplier', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        $stockByCategory = Category::select('categories.id', 'categories.name as category')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->whereNull('products.deleted_at')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('COALESCE(SUM(products.quantity * products.cost_price), 0) as value')
            ->get();

        return response()->json([
            'data' => [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_suppliers' => $totalSuppliers,
                'total_stock_value' => round($totalStockValue, 2),
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'recent_movements' => $recentMovements,
                'recent_purchase_orders' => $recentOrders,
                'stock_by_category' => $stockByCategory,
            ],
        ]);
    }
}
