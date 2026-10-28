<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'order_number',
        'supplier_id',
        'user_id',
        'status',
        'subtotal',
        'tax',
        'total',
        'notes',
        'expected_date',
        'received_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'expected_date' => 'date',
        'received_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = "PO-{$today}-";

        $lastOrder = static::where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('order_number')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
