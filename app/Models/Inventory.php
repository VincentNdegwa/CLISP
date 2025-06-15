<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'bin_location_id',
        'quantity',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'business_id',
        'status', // active, inactive, discontinued
    ];

    public function item()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function batches()
    {
        return $this->hasMany(InventoryBatch::class);
    }
}
