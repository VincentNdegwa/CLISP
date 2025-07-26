<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'purchase_order_id',
        'receipt_number',
        'receipt_date',
        'received_by',
        'inspected_by',
        'warehouse_id',
        'status', // pending, inspected, completed, rejected
        'notes',
        'carrier',
        'tracking_number',
        'delivery_note_number',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function getTotalQuantityReceivedAttribute()
    {
        return $this->items()->sum('quantity_received');
    }

    public function getTotalQuantityRejectedAttribute()
    {
        return $this->items()->sum('quantity_rejected');
    }
}
