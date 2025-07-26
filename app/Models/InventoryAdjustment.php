<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'adjustment_number',
        'adjustment_date',
        'warehouse_id',
        'adjustment_type', // count, damage, expiry, theft, loss, return, correction
        'reference_type', // stock_count, quality_check, etc.
        'reference_id',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'status', // draft, pending_approval, approved, rejected
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryAdjustmentItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    // Helper methods
    public function getTotalAdjustmentValueAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_cost;
        });
    }

    public function getNetQuantityChangeAttribute()
    {
        return $this->items->sum('quantity');
    }
}
