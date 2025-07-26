<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceiptItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'goods_receipt_id',
        'purchase_order_item_id',
        'item_id',
        'batch_number',
        'lot_number',
        'quantity_received',
        'quantity_rejected',
        'rejection_reason',
        'expiry_date',
        'manufacturing_date',
        'bin_location_id',
        'status', // pending, inspected, stored, rejected
        'notes',
        'barcode',
        'qr_code',
        'rfid_tag',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'manufacturing_date' => 'date',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function item()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id');
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class);
    }

    public function inventoryBatch()
    {
        return $this->hasOne(InventoryBatch::class, 'goods_receipt_item_id');
    }

    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return now()->greaterThan($this->expiry_date);
    }

    public function daysUntilExpiry()
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        return now()->diffInDays($this->expiry_date, false);
    }
}
