<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockCountItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stock_count_id',
        'item_id',
        'batch_id',
        'bin_location_id',
        'system_quantity',
        'counted_quantity',
        'unit_cost',
        'counted_by',
        'counted_at',
        'verified_by',
        'verified_at',
        'notes',
        'status', // pending, counted, verified, adjusted
    ];

    protected $casts = [
        'counted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function stockCount()
    {
        return $this->belongsTo(StockCount::class);
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

    public function counter()
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getDiscrepancyAttribute()
    {
        if ($this->counted_quantity === null) {
            return null;
        }
        
        return $this->counted_quantity - $this->system_quantity;
    }

    public function getDiscrepancyPercentageAttribute()
    {
        if ($this->system_quantity == 0 || $this->counted_quantity === null) {
            return null;
        }
        
        return round((($this->counted_quantity - $this->system_quantity) / $this->system_quantity) * 100, 2);
    }

    public function getDiscrepancyValueAttribute()
    {
        if ($this->counted_quantity === null) {
            return null;
        }
        
        return abs($this->counted_quantity - $this->system_quantity) * $this->unit_cost;
    }

    public function hasDiscrepancy()
    {
        if ($this->counted_quantity === null) {
            return false;
        }
        
        return $this->counted_quantity != $this->system_quantity;
    }
}
