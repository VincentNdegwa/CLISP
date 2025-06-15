<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inventory_id',
        'batch_number',
        'lot_number',
        'quantity',
        'manufacturing_date',
        'expiry_date',
        'cost_price',
        'supplier_id',
        'purchase_order_id',
        'received_date',
        'status', // available, reserved, sold, expired, damaged
        'barcode',
        'qr_code',
        'rfid_tag',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function reservations()
    {
        return $this->hasMany(BatchReservation::class);
    }

    // Helper methods for FIFO/LIFO/FEFO
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
