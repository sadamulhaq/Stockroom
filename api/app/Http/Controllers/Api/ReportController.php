<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function combined(Request $request): JsonResponse
    {
        $period = (int) $request->get('period', 30);
        $from = now()->subDays($period)->toDateString();
        $to = now()->toDateString();

        // Inventory Summary
        $totalProducts = Product::count();
        $totalValue = Product::sum(DB::raw('quantity * cost_price'));
        $byCategory = Category::select('categories.name as category')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->whereNull('products.deleted_at')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('COUNT(products.id) as products')
            ->selectRaw('COALESCE(SUM(products.quantity * products.cost_price), 0) as value')
            ->get();

        // Stock Movement Stats
        $movementSummary = StockMovement::select('type')
            ->selectRaw('SUM(ABS(quantity)) as total')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('type')
            ->pluck('total', 'type');
        $movementStats = [
            'in' => (int) ($movementSummary['in'] ?? 0),
            'out' => (int) ($movementSummary['out'] ?? 0),
            'adjustment' => (int) ($movementSummary['adjustment'] ?? 0),
            'return' => (int) ($movementSummary['return'] ?? 0),
        ];

        // Movement chart data
        $daily = StockMovement::select(DB::raw('DATE(created_at) as date'), 'type')
            ->selectRaw('SUM(ABS(quantity)) as total')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        $labels = [];
        $inData = [];
        $outData = [];
        $currentDate = now()->subDays($period);
        while ($currentDate->lte(now())) {
            $dateStr = $currentDate->toDateString();
            $labels[] = $dateStr;
            $dayData = $daily->get($dateStr);
            $inData[] = $dayData ? (int) $dayData->where('type', 'in')->sum('total') : 0;
            $outData[] = $dayData ? (int) $dayData->where('type', 'out')->sum('total') : 0;
            $currentDate->addDay();
        }

        // Top products
        $topByValue = Product::select('name', 'sku')
            ->selectRaw('(quantity * cost_price) as value')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        $topByMovement = Product::select('products.name', 'products.sku')
            ->selectRaw('COUNT(stock_movements.id) as movements')
            ->leftJoin('stock_movements', 'products.id', '=', 'stock_movements.product_id')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('movements')
            ->limit(10)
            ->get();

        // Purchase order stats
        $ordersByStatus = PurchaseOrder::select('status')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total) as total_value')
            ->groupBy('status')
            ->pluck('count', 'status');
        $totalSpend = PurchaseOrder::where('status', 'received')->sum('total');

        return response()->json([
            'data' => [
                'summary' => [
                    'total_products' => $totalProducts,
                    'total_value' => round($totalValue, 2),
                    'by_category' => $byCategory,
                ],
                'movement_stats' => $movementStats,
                'movement_chart' => [
                    'labels' => $labels,
                    'in' => $inData,
                    'out' => $outData,
                ],
                'top_by_value' => $topByValue,
                'top_by_movement' => $topByMovement,
                'order_stats' => [
                    'draft' => (int) ($ordersByStatus['draft'] ?? 0),
                    'pending' => (int) ($ordersByStatus['pending'] ?? 0),
                    'approved' => (int) ($ordersByStatus['approved'] ?? 0),
                    'received' => (int) ($ordersByStatus['received'] ?? 0),
                    'cancelled' => (int) ($ordersByStatus['cancelled'] ?? 0),
                    'total_spend' => round($totalSpend, 2),
                ],
            ],
        ]);
    }
    public function inventorySummary(): JsonResponse
    {
        $totalProducts = Product::count();
        $totalValue = Product::sum(DB::raw('quantity * cost_price'));
        $lowStockCount = Product::lowStock()->where('quantity', '>', 0)->count();
        $outOfStockCount = Product::where('quantity', 0)->count();

        $byCategory = Category::select('categories.id', 'categories.name')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->whereNull('products.deleted_at')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('COUNT(products.id) as products_count')
            ->selectRaw('COALESCE(SUM(products.quantity * products.cost_price), 0) as total_value')
            ->selectRaw('COALESCE(SUM(products.quantity), 0) as total_quantity')
            ->get();

        return response()->json([
            'data' => [
                'total_products' => $totalProducts,
                'total_value' => round($totalValue, 2),
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'by_category' => $byCategory,
            ],
        ]);
    }

    public function stockMovements(Request $request): JsonResponse
    {
        $from = $request->get('from', now()->subMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $summary = StockMovement::select('type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(ABS(quantity)) as total_quantity')
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->groupBy('type')
            ->get();

        $daily = StockMovement::select(DB::raw('DATE(created_at) as date'), 'type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(ABS(quantity)) as total_quantity')
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        return response()->json([
            'data' => [
                'period' => ['from' => $from, 'to' => $to],
                'summary' => $summary,
                'daily' => $daily,
            ],
        ]);
    }

    public function topProducts(Request $request): JsonResponse
    {
        $sortBy = $request->get('sort_by', 'value');
        $limit = $request->get('limit', 10);

        if ($sortBy === 'value') {
            $products = Product::with('category')
                ->selectRaw('*, (quantity * cost_price) as stock_value')
                ->orderByDesc('stock_value')
                ->limit($limit)
                ->get();
        } else {
            $products = Product::with('category')
                ->withCount('stockMovements')
                ->orderByDesc('stock_movements_count')
                ->limit($limit)
                ->get();
        }

        return response()->json(['data' => $products]);
    }

    public function purchaseOrders(Request $request): JsonResponse
    {
        $from = $request->get('from', now()->subMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $byStatus = PurchaseOrder::select('status')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total) as total_value')
            ->groupBy('status')
            ->get();

        $byPeriod = PurchaseOrder::select(DB::raw('DATE(created_at) as date'))
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(total) as total_value')
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'data' => [
                'by_status' => $byStatus,
                'by_period' => $byPeriod,
            ],
        ]);
    }
}
