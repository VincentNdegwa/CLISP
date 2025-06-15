<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryAllocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sales_order_item_id',
        'batch_id',
        'bin_location_id',
        'quantity',
        'allocated_by',
        'allocated_at',
        'picked_quantity',
        'picked_by',
        'picked_at',
        'status', // allocated, picked, packed, shipped, cancelled
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
        'picked_at' => 'datetime',
    ];

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class);
    }

    public function binLocation()
    {
        return $this->belongsTo(BinLocation::class);
    }

    public function allocator()
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }

    public function picker()
    {
        return $this->belongsTo(User::class, 'picked_by');
    }

    public function getRemainingQuantityAttribute()
    {
        return max(0, $this->quantity - $this->picked_quantity);
    }

    public function isFullyPicked()
    {
        return $this->picked_quantity >= $this->quantity;
    }
}
