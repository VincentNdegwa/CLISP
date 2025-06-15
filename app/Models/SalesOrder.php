<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_DRAFT = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_PICKING = 3;
    const STATUS_PACKING = 4;
    const STATUS_SHIPPED = 5;
    const STATUS_DELIVERED = 6;
    const STATUS_CANCELLED = 7;
    const STATUS_ON_HOLD = 8;

    // Payment status constants
    const PAYMENT_PENDING = 0;
    const PAYMENT_PARTIAL = 1;
    const PAYMENT_PAID = 2;
    const PAYMENT_REFUNDED = 3;

    // Fulfillment status constants
    const FULFILLMENT_PENDING = 0;
    const FULFILLMENT_PARTIAL = 1;
    const FULFILLMENT_FULFILLED = 2;
    const FULFILLMENT_BACKORDERED = 3;

    // Status text mapping
    public static $statusText = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_CONFIRMED => 'Confirmed',
        self::STATUS_PROCESSING => 'Processing',
        self::STATUS_PICKING => 'Picking',
        self::STATUS_PACKING => 'Packing',
        self::STATUS_SHIPPED => 'Shipped',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_ON_HOLD => 'On Hold',
    ];

    // Payment status text mapping
    public static $paymentStatusText = [
        self::PAYMENT_PENDING => 'Pending',
        self::PAYMENT_PARTIAL => 'Partial',
        self::PAYMENT_PAID => 'Paid',
        self::PAYMENT_REFUNDED => 'Refunded',
    ];

    // Fulfillment status text mapping
    public static $fulfillmentStatusText = [
        self::FULFILLMENT_PENDING => 'Pending',
        self::FULFILLMENT_PARTIAL => 'Partial',
        self::FULFILLMENT_FULFILLED => 'Fulfilled',
        self::FULFILLMENT_BACKORDERED => 'Backordered',
    ];

    // Status CSS class mapping
    public static $statusClass = [
        self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
        self::STATUS_CONFIRMED => 'bg-blue-100 text-blue-800',
        self::STATUS_PROCESSING => 'bg-indigo-100 text-indigo-800',
        self::STATUS_PICKING => 'bg-purple-100 text-purple-800',
        self::STATUS_PACKING => 'bg-pink-100 text-pink-800',
        self::STATUS_SHIPPED => 'bg-orange-100 text-orange-800',
        self::STATUS_DELIVERED => 'bg-green-100 text-green-800',
        self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
        self::STATUS_ON_HOLD => 'bg-yellow-100 text-yellow-800',
    ];

    // Payment status CSS class mapping
    public static $paymentStatusClass = [
        self::PAYMENT_PENDING => 'bg-yellow-100 text-yellow-800',
        self::PAYMENT_PARTIAL => 'bg-blue-100 text-blue-800',
        self::PAYMENT_PAID => 'bg-green-100 text-green-800',
        self::PAYMENT_REFUNDED => 'bg-red-100 text-red-800',
    ];

    // Fulfillment status CSS class mapping
    public static $fulfillmentStatusClass = [
        self::FULFILLMENT_PENDING => 'bg-yellow-100 text-yellow-800',
        self::FULFILLMENT_PARTIAL => 'bg-blue-100 text-blue-800',
        self::FULFILLMENT_FULFILLED => 'bg-green-100 text-green-800',
        self::FULFILLMENT_BACKORDERED => 'bg-red-100 text-red-800',
    ];

    protected $fillable = [
        'business_id',
        'order_number',
        'customer_id',
        'order_date',
        'required_date',
        'shipping_date',
        'status',
        'payment_status',
        'fulfillment_status',
        'shipping_method',
        'shipping_cost',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency_code',
        'notes',
        'created_by',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_postal_code',
        'tracking_number',
        'priority', // low, normal, high, urgent
    ];

    protected $casts = [
        'order_date' => 'date',
        'required_date' => 'date',
        'shipping_date' => 'date',
        'status' => 'integer',
        'payment_status' => 'integer',
        'fulfillment_status' => 'integer',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    // Helper methods
    public function isBackordered()
    {
        return $this->items()->where('backorder_quantity', '>', 0)->exists();
    }

    public function isFullyAllocated()
    {
        foreach ($this->items as $item) {
            if ($item->quantity > $item->allocated_quantity) {
                return false;
            }
        }
        
        return true;
    }

    public function isFullyShipped()
    {
        foreach ($this->items as $item) {
            if ($item->quantity > $item->shipped_quantity) {
                return false;
            }
        }
        
        return true;
    }

    public function getFulfillmentRateAttribute()
    {
        $totalOrdered = $this->items->sum('quantity');
        
        if ($totalOrdered <= 0) {
            return 0;
        }
        
        $totalShipped = $this->items->sum('shipped_quantity');
        
        return round(($totalShipped / $totalOrdered) * 100, 2);
    }

    public function getStatusTextAttribute()
    {
        return self::$statusText[$this->status] ?? 'Unknown';
    }

    public function getStatusClassAttribute()
    {
        return self::$statusClass[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getPaymentStatusTextAttribute()
    {
        return self::$paymentStatusText[$this->payment_status] ?? 'Unknown';
    }

    public function getPaymentStatusClassAttribute()
    {
        return self::$paymentStatusClass[$this->payment_status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFulfillmentStatusTextAttribute()
    {
        return self::$fulfillmentStatusText[$this->fulfillment_status] ?? 'Unknown';
    }

    public function getFulfillmentStatusClassAttribute()
    {
        return self::$fulfillmentStatusClass[$this->fulfillment_status] ?? 'bg-gray-100 text-gray-800';
    }
}
