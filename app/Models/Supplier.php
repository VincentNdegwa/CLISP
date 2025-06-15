<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'name',
        'code',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'tax_id',
        'payment_terms',
        'lead_time_days',
        'status', // active, inactive, blacklisted
        'notes',
        'website',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(SupplierItem::class);
    }

    public function inventoryBatches()
    {
        return $this->hasMany(InventoryBatch::class);
    }

    // Performance metrics
    public function getOnTimeDeliveryRate()
    {
        $totalOrders = $this->purchaseOrders()->whereIn('status', ['completed', 'late'])->count();
        
        if ($totalOrders === 0) {
            return 0;
        }
        
        $onTimeOrders = $this->purchaseOrders()->where('status', 'completed')->count();
        
        return round(($onTimeOrders / $totalOrders) * 100, 2);
    }

    public function getQualityRate()
    {
        $totalReceived = $this->purchaseOrders()
            ->whereIn('status', ['completed', 'partially_received'])
            ->sum('total_quantity_received');
        
        if ($totalReceived === 0) {
            return 0;
        }
        
        $qualityIssues = $this->purchaseOrders()
            ->whereIn('status', ['completed', 'partially_received'])
            ->sum('quantity_rejected');
        
        return round(100 - (($qualityIssues / $totalReceived) * 100), 2);
    }
}
