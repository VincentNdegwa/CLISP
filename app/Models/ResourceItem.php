<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceItem extends Model
{
    use HasFactory;
    protected $table = 'resource_item';
    protected $fillable = [
        'business_id',
        'item_name',
        'description',
        'category_id',
        'quantity',
        'unit',
        'price',
        'item_image',
    ];


    public function category()
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function itemsBusiness()
    {
        return $this->hasMany(ItemBusiness::class, 'item_id');
    }
}
