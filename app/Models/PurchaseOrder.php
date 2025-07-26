<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 0;
    const STATUS_SUBMITTED = 1;
    const STATUS_APPROVED = 2;
    const STATUS_SENT = 3;
    const STATUS_PARTIAL = 4;
    const STATUS_RECEIVED = 5;
    const STATUS_CANCELLED = 6;
    const STATUS_ON_HOLD = 7;

    public static $statusText = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_SUBMITTED => 'Submitted',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_SENT => 'Sent',
        self::STATUS_PARTIAL => 'Partially Received',
        self::STATUS_RECEIVED => 'Received',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_ON_HOLD => 'On Hold',
    ];

    public static $statusClass = [
        self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
        self::STATUS_SUBMITTED => 'bg-blue-100 text-blue-800',
        self::STATUS_APPROVED => 'bg-indigo-100 text-indigo-800',
        self::STATUS_SENT => 'bg-purple-100 text-purple-800',
        self::STATUS_PARTIAL => 'bg-amber-100 text-amber-800',
        self::STATUS_RECEIVED => 'bg-green-100 text-green-800',
        self::STATUS_CANCELLED => 'bg-red-100 text-red-800',
        self::STATUS_ON_HOLD => 'bg-yellow-100 text-yellow-800',
    ];

    protected $fillable = [
        'business_id',
        'supplier_id',
        'po_number',
        'order_date',
        'expected_delivery_date',
        'delivery_date',
        'status', 
        'payment_status', // pending, partial, paid, overdue
        'payment_terms',
        'shipping_method',
        'shipping_cost',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency_code',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'total_quantity_ordered',
        'total_quantity_received',
        'quantity_rejected',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivery_date' => 'date',
        'approved_at' => 'datetime',
        'status' => 'integer',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

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
        if ($this->total_quantity_ordered <= 0) {
            return 0;
        }
        
        return round(($this->total_quantity_received / $this->total_quantity_ordered) * 100, 2);
    }

    public function isOverdue()
    {
        return $this->status !== self::STATUS_RECEIVED && 
               $this->status !== self::STATUS_CANCELLED && 
               $this->expected_delivery_date && 
               now()->greaterThan($this->expected_delivery_date);
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->expected_delivery_date);
    }
}
