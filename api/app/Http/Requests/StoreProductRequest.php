<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products,sku,' . $productId,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'min_stock_level' => 'sometimes|integer|min:0',
            'max_stock_level' => 'sometimes|integer|min:0',
            'unit' => 'sometimes|string|max:50',
            'location' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
