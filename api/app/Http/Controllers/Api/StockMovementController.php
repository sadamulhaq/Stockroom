<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::with(['product', 'user']);

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($from = $request->get('from', $request->get('date_from'))) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to', $request->get('date_to'))) {
            $query->whereDate('created_at', '<=', $to);
        }

        $movements = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($movements);
    }

    public function byProduct(int $productId): JsonResponse
    {
        $movements = StockMovement::with(['user'])
            ->where('product_id', $productId)
            ->latest()
            ->paginate(15);

        return response()->json($movements);
    }
}
