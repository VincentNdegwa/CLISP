<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_SHIPPED = 2;
    const STATUS_DELIVERED = 3;
    const STATUS_RETURNED = 4;

    // Status text mapping
    public static $statusText = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_PROCESSING => 'Processing',
        self::STATUS_SHIPPED => 'Shipped',
        self::STATUS_DELIVERED => 'Delivered',
        self::STATUS_RETURNED => 'Returned',
    ];

    // Status CSS class mapping
    public static $statusClass = [
        self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
        self::STATUS_PROCESSING => 'bg-blue-100 text-blue-800',
        self::STATUS_SHIPPED => 'bg-purple-100 text-purple-800',
        self::STATUS_DELIVERED => 'bg-green-100 text-green-800',
        self::STATUS_RETURNED => 'bg-red-100 text-red-800',
    ];

    protected $fillable = [
        'business_id',
        'sales_order_id',
        'shipment_number',
        'shipment_date',
        'carrier',
        'tracking_number',
        'shipping_method',
        'shipping_cost',
        'status', 
        'notes',
        'created_by',
        'packed_by',
        'packed_at',
        'shipped_by',
        'shipped_at',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_postal_code',
        'package_weight',
        'package_dimensions',
        'estimated_delivery_date',
        'actual_delivery_date',
    ];

    protected $casts = [
        'shipment_date' => 'date',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'estimated_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'status' => 'integer',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function packer()
    {
        return $this->belongsTo(User::class, 'packed_by');
    }

    public function shipper()
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    // Helper methods
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getStatusTextAttribute()
    {
        return self::$statusText[$this->status] ?? 'Unknown';
    }

    public function getStatusClassAttribute()
    {
        return self::$statusClass[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getTrackingUrlAttribute()
    {
        // This would be implemented based on carrier-specific tracking URL formats
        // Example for a generic implementation
        switch (strtolower($this->carrier)) {
            case 'ups':
                return "https://www.ups.com/track?tracknum={$this->tracking_number}";
            case 'fedex':
                return "https://www.fedex.com/apps/fedextrack/?tracknumbers={$this->tracking_number}";
            case 'usps':
                return "https://tools.usps.com/go/TrackConfirmAction?tLabels={$this->tracking_number}";
            case 'dhl':
                return "https://www.dhl.com/en/express/tracking.html?AWB={$this->tracking_number}";
            default:
                return null;
        }
    }

    public function isDelivered()
    {
        return $this->status === self::STATUS_DELIVERED && $this->actual_delivery_date !== null;
    }

    public function isOverdue()
    {
        return $this->status !== self::STATUS_DELIVERED && 
               $this->estimated_delivery_date && 
               now()->greaterThan($this->estimated_delivery_date);
    }
}
