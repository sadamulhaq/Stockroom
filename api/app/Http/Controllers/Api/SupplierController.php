<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::withCount('products');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($suppliers);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        return response()->json(['data' => $supplier], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load(['products' => function ($q) {
            $q->limit(10);
        }]);
        $supplier->loadCount('products');
        $supplier->load(['purchaseOrders' => function ($q) {
            $q->latest()->limit(5);
        }]);

        return response()->json(['data' => $supplier]);
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return response()->json(['data' => $supplier]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        if ($supplier->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete supplier with associated products.',
            ], 422);
        }

        $supplier->delete();

        return response()->json(['message' => 'Supplier deleted successfully.']);
    }
}
