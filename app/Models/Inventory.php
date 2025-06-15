<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    // Define status constants
    const STATUS_IN_STOCK = 0;
    const STATUS_LOW_STOCK = 1;
    const STATUS_OUT_OF_STOCK = 2;
    const STATUS_RESERVED = 3;
    const STATUS_DAMAGED = 4;
    const STATUS_EXPIRED = 5;

    // Define static text mapping for statuses
    public static $statusText = [
        self::STATUS_IN_STOCK => 'In Stock',
        self::STATUS_LOW_STOCK => 'Low Stock',
        self::STATUS_OUT_OF_STOCK => 'Out of Stock',
        self::STATUS_RESERVED => 'Reserved',
        self::STATUS_DAMAGED => 'Damaged',
        self::STATUS_EXPIRED => 'Expired',
    ];

    // Define static class mapping for status styling
    public static $statusClass = [
        self::STATUS_IN_STOCK => 'bg-green-100 text-green-800',
        self::STATUS_LOW_STOCK => 'bg-yellow-100 text-yellow-800',
        self::STATUS_OUT_OF_STOCK => 'bg-red-100 text-red-800',
        self::STATUS_RESERVED => 'bg-blue-100 text-blue-800',
        self::STATUS_DAMAGED => 'bg-purple-100 text-purple-800',
        self::STATUS_EXPIRED => 'bg-gray-100 text-gray-800',
    ];

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'bin_location_id',
        'quantity',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'business_id',
        'status',
        'batch_number',
        'reorder_quantity',
        'expiry_date',
        'cost_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'expiry_date' => 'date',
        'status' => 'integer',
    ];

    public function getStatusTextAttribute()
    {
        return self::$statusText[$this->status] ?? 'Unknown';
    }

    public function getStatusClassAttribute()
    {
        return self::$statusClass[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function item()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }
}
