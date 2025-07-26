<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustmentReason extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'business_id',
        'is_active',
        'type', // 'increase', 'decrease', 'both'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockMovement::class, 'reason_id');
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where(function($query) use ($businessId) {
            $query->where('business_id', $businessId)
                  ->orWhere('business_id', 0); 
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
