<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockCount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'count_number',
        'count_date',
        'warehouse_id',
        'zone_id',
        'count_type', // cycle, full, partial, blind
        'status', // draft, in_progress, completed, cancelled
        'notes',
        'created_by',
        'completed_by',
        'completed_at',
        'scheduled_date',
    ];

    protected $casts = [
        'count_date' => 'date',
        'completed_at' => 'datetime',
        'scheduled_date' => 'date',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function zone()
    {
        return $this->belongsTo(WarehouseZone::class);
    }

    public function items()
    {
        return $this->hasMany(StockCountItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function adjustments()
    {
        return $this->morphMany(InventoryAdjustment::class, 'reference');
    }

    // Helper methods
    public function getCompletionPercentageAttribute()
    {
        $total = $this->items()->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $counted = $this->items()->whereNotNull('counted_quantity')->count();
        
        return round(($counted / $total) * 100, 2);
    }

    public function getDiscrepancyCountAttribute()
    {
        return $this->items()->whereRaw('ABS(system_quantity - counted_quantity) > 0')->count();
    }

    public function getDiscrepancyValueAttribute()
    {
        return $this->items()->sum(function ($item) {
            return abs($item->system_quantity - $item->counted_quantity) * $item->unit_cost;
        });
    }
}
