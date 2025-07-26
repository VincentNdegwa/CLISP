<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryAdjustmentItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inventory_adjustment_id',
        'item_id',
        'batch_id',
        'bin_location_id',
        'quantity',
        'unit_cost',
        'reason_code', // damaged, expired, lost, found, miscounted, etc.
        'notes',
    ];

    public function adjustment()
    {
        return $this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id');
    }

    public function item()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id');
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class);
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->unit_cost;
    }
}
