<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['supplier', 'user'])->withCount('items');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($supplierId = $request->get('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($orders);
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemTotal;
                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $itemTotal,
                ];
            }

            $tax = $validated['tax'] ?? 0;
            $total = $subtotal + $tax;

            $order = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => $request->user()->id,
                'status' => 'draft',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'expected_date' => $validated['expected_date'] ?? null,
            ]);

            $order->items()->createMany($itemsData);

            return $order;
        });

        return response()->json([
            'data' => $order->load(['items.product', 'supplier', 'user']),
        ], 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load(['items.product', 'supplier', 'user']);

        return response()->json(['data' => $purchaseOrder]);
    }

    public function update(StorePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return response()->json([
                'message' => 'Only draft or pending orders can be updated.',
            ], 422);
        }

        $validated = $request->validated();

        $order = DB::transaction(function () use ($validated, $purchaseOrder) {
            $purchaseOrder->items()->delete();

            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemTotal;
                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $itemTotal,
                ];
            }

            $tax = $validated['tax'] ?? 0;
            $total = $subtotal + $tax;

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'expected_date' => $validated['expected_date'] ?? null,
            ]);

            $purchaseOrder->items()->createMany($itemsData);

            return $purchaseOrder;
        });

        return response()->json([
            'data' => $order->load(['items.product', 'supplier', 'user']),
        ]);
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft orders can be deleted.',
            ], 422);
        }

        $purchaseOrder->delete();

        return response()->json(['message' => 'Purchase order deleted successfully.']);
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,pending,approved,received,cancelled',
        ]);

        $newStatus = $validated['status'];

        if ($newStatus === 'received') {
            DB::transaction(function () use ($purchaseOrder, $request) {
                $purchaseOrder->load('items.product');

                foreach ($purchaseOrder->items as $item) {
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'user_id' => $request->user()->id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                        'reason' => 'Purchase Order received: ' . $purchaseOrder->order_number,
                    ]);

                    $item->product->increment('quantity', $item->quantity);
                    $item->update(['received_quantity' => $item->quantity]);
                }

                $purchaseOrder->update([
                    'status' => 'received',
                    'received_date' => now(),
                ]);
            });
        } else {
            $purchaseOrder->update(['status' => $newStatus]);
        }

        return response()->json([
            'data' => $purchaseOrder->fresh()->load(['items.product', 'supplier', 'user']),
        ]);
    }
}
