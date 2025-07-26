<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'item_id',
        'batch_id',
        'from_warehouse_id',
        'from_bin_location_id',
        'to_warehouse_id',
        'to_bin_location_id',
        'quantity',
        'movement_type', // receipt, transfer, sale, return, adjustment, disposal
        'reference_type', // purchase_order, sales_order, stock_transfer, stock_adjustment, etc.
        'reference_id',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function item()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id');
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function fromBinLocation()
    {
        return $this->belongsTo(BinLocation::class, 'from_bin_location_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function toBinLocation()
    {
        return $this->belongsTo(BinLocation::class, 'to_bin_location_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Polymorphic relationship to reference
    public function reference()
    {
        return $this->morphTo();
    }
}
