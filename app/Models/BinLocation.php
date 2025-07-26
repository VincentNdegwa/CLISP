<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BinLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'zone_id',
        'name',
        'code',
        'aisle',
        'rack',
        'shelf',
        'bin',
        'capacity',
        'capacity_unit',
        'status', // active, inactive, full, maintenance
        'location_type', // standard, receiving, shipping, quarantine, returns
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function zone()
    {
        return $this->belongsTo(WarehouseZone::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function isAvailable()
    {
        return $this->status === 'active' && $this->inventory()->sum('quantity') < $this->capacity;
    }

    public function getAvailableCapacity()
    {
        $used = $this->inventory()->sum('quantity');
        return $this->capacity - $used;
    }

    public function getUtilizationPercentage()
    {
        if ($this->capacity <= 0) {
            return 0;
        }
        
        $used = $this->inventory()->sum('quantity');
        return min(100, round(($used / $this->capacity) * 100, 2));
    }
}
