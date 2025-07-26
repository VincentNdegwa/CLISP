<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseZone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'name',
        'code',
        'description',
        'zone_type', // storage, picking, packing, receiving, shipping, returns, quarantine
        'temperature_controlled',
        'min_temperature',
        'max_temperature',
        'temperature_unit',
        'status', // active, inactive, maintenance
    ];

    protected $casts = [
        'temperature_controlled' => 'boolean',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function binLocations()
    {
        return $this->hasMany(BinLocation::class, 'zone_id');
    }
}
