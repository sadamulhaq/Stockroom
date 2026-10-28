<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('tree')) {
            $categories = Category::with('children.children')
                ->whereNull('parent_id')
                ->withCount('products')
                ->get();

            return response()->json(['data' => $categories]);
        }

        $categories = Category::withCount('products')
            ->with('parent')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return response()->json(['data' => $category->load('parent')], 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->loadCount('products')->load(['parent', 'children']);

        return response()->json(['data' => $category]);
    }

    public function update(StoreCategoryRequest $request, Category $category): JsonResponse
    {
        $category->update($request->validated());

        return response()->json(['data' => $category->load('parent')]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated products.',
            ], 422);
        }

        if ($category->children()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with subcategories.',
            ], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
