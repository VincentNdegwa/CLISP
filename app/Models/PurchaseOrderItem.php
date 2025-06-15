<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_PARTIAL = 1;
    const STATUS_RECEIVED = 2;
    const STATUS_CANCELLED = 3;
    const STATUS_BACKORDERED = 4;

    // Status text mapping
    public static $statusText = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_PARTIAL => 'Partially Received',
        self::STATUS_RECEIVED => 'Received',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_BACKORDERED => 'Backordered',
    ];

    // Status CSS class mapping
    public static $statusClass = [
        self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
        self::STATUS_PARTIAL => 'bg-blue-100 text-blue-800',
        self::STATUS_RECEIVED => 'bg-green-100 text-green-800',
        self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
        self::STATUS_BACKORDERED => 'bg-amber-100 text-amber-800',
    ];

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'description',
        'quantity_ordered',
        'quantity_received',
        'quantity_rejected',
        'unit_price',
        'tax_rate',
        'discount_amount',
        'total_amount',
        'currency_code',
        'expected_delivery_date',
        'status', 
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'status' => 'integer',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id');
    }

    public function receipts()
    {
        return $this->hasMany(GoodsReceiptItem::class);
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

    public function getReceiptRateAttribute()
    {
        if ($this->quantity_ordered <= 0) {
            return 0;
        }
        
        return round(($this->quantity_received / $this->quantity_ordered) * 100, 2);
    }

    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }

    public function isFullyReceived()
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }
}
