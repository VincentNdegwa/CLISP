<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipmentItem extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_SHIPPED = 1;
    const STATUS_DELIVERED = 2;
    const STATUS_RETURNED = 3;

    // Status text mapping
    public static $statusText = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_SHIPPED => 'Shipped',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_RETURNED => 'Returned',
    ];

    // Status CSS class mapping
    public static $statusClass = [
        self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
        self::STATUS_SHIPPED => 'bg-purple-100 text-purple-800',
        self::STATUS_DELIVERED => 'bg-green-100 text-green-800',
        self::STATUS_RETURNED => 'bg-red-100 text-red-800',
    ];

    protected $fillable = [
        'shipment_id',
        'sales_order_item_id',
        'item_id',
        'batch_id',
        'quantity',
        'notes',
        'status', 
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function item()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id');
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
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
}
