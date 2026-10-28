<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'supplier']);

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($supplierId = $request->get('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        if ($stockStatus = $request->get('stock_status')) {
            if ($stockStatus === 'low') {
                $query->lowStock()->where('quantity', '>', 0);
            } elseif ($stockStatus === 'out') {
                $query->where('quantity', '<=', 0);
            } elseif ($stockStatus === 'in') {
                $query->where('quantity', '>', 0);
            }
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json(['data' => $product->load(['category', 'supplier'])], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'supplier']);
        $product->load(['stockMovements' => function ($q) {
            $q->with('user')->latest()->limit(20);
        }]);

        return response()->json(['data' => $product]);
    }

    public function update(StoreProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json(['data' => $product->load(['category', 'supplier'])]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $products = Product::with(['category', 'supplier'])
            ->lowStock()
            ->orderBy('quantity')
            ->paginate($request->get('per_page', 15));

        return response()->json($products);
    }

    public function adjustStock(AdjustStockRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $product, $request) {
            $quantityChange = $validated['quantity'];

            if ($validated['type'] === 'out') {
                $quantityChange = -$quantityChange;
            }

            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $request->user()->id,
                'type' => $validated['type'],
                'quantity' => $quantityChange,
                'reference_type' => 'manual',
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $product->increment('quantity', $quantityChange);
        });

        $product->refresh();

        return response()->json([
            'message' => 'Stock adjusted successfully.',
            'data' => $product,
        ]);
    }
}
