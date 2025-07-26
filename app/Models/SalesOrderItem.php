<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_ALLOCATED = 1;
    const STATUS_PARTIAL = 2;
    const STATUS_PICKED = 3;
    const STATUS_PACKED = 4;
    const STATUS_SHIPPED = 5;
    const STATUS_DELIVERED = 6;
    const STATUS_CANCELLED = 7;
    const STATUS_BACKORDERED = 8;

    // Status text mapping
    public static $statusText = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_ALLOCATED => 'Allocated',
        self::STATUS_PARTIAL => 'Partial',
        self::STATUS_PICKED => 'Picked',
        self::STATUS_PACKED => 'Packed',
        self::STATUS_SHIPPED => 'Shipped',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_BACKORDERED => 'Backordered',
    ];

    // Status CSS class mapping
    public static $statusClass = [
        self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
        self::STATUS_ALLOCATED => 'bg-blue-100 text-blue-800',
        self::STATUS_PARTIAL => 'bg-indigo-100 text-indigo-800',
        self::STATUS_PICKED => 'bg-purple-100 text-purple-800',
        self::STATUS_PACKED => 'bg-pink-100 text-pink-800',
        self::STATUS_SHIPPED => 'bg-orange-100 text-orange-800',
        self::STATUS_DELIVERED => 'bg-green-100 text-green-800',
        self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
        self::STATUS_BACKORDERED => 'bg-amber-100 text-amber-800',
    ];

    protected $fillable = [
        'sales_order_id',
        'item_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'discount_amount',
        'total_amount',
        'currency_code',
        'allocated_quantity',
        'picked_quantity',
        'packed_quantity',
        'shipped_quantity',
        'backorder_quantity',
        'returned_quantity',
        'status', // pending, allocated, picked, packed, shipped, delivered, backordered, cancelled, returned
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'quantity' => 'decimal:2',
        'allocated_quantity' => 'decimal:2',
        'picked_quantity' => 'decimal:2',
        'packed_quantity' => 'decimal:2',
        'shipped_quantity' => 'decimal:2',
        'backorder_quantity' => 'decimal:2',
        'returned_quantity' => 'decimal:2',
        'status' => 'integer',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id');
    }

    public function allocations()
    {
        return $this->hasMany(InventoryAllocation::class);
    }

    public function shipmentItems()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    // Helper methods
    public function getStatusTextAttribute()
    {
        return self::$statusText[$this->status] ?? 'Unknown';
    }

    public function getStatusClassAttribute()
    {
        return self::$statusClass[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity - $this->shipped_quantity);
    }

    public function getAllocationPercentageAttribute()
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return round(($this->allocated_quantity / $this->quantity) * 100, 2);
    }

    public function getFulfillmentPercentageAttribute()
    {
        if ($this->quantity <= 0) {
            return 0;
        }
        
        return round(($this->shipped_quantity / $this->quantity) * 100, 2);
    }

    public function isFullyAllocated()
    {
        return $this->allocated_quantity >= $this->quantity;
    }

    public function isFullyShipped()
    {
        return $this->shipped_quantity >= $this->quantity;
    }

    public function isBackordered()
    {
        return $this->backorder_quantity > 0;
    }
}
