<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'contact_person',
        'contact_phone',
        'contact_email',
        'business_id',
        'status', // active, inactive
        'is_default',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function binLocations()
    {
        return $this->hasMany(BinLocation::class);
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function zones()
    {
        return $this->hasMany(WarehouseZone::class);
    }
}
