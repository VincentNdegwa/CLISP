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
        'date_added',
        'item_image',
    ];


    public function category()
    {
        return $this->belongsTo(ResourceCategory::class, 'category_id');
    }
}
