<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceCategory extends Model
{
    use HasFactory;
    protected $table = 'resource_category';

    protected $fillable = [
        "business_id",
        "name",
        "description",
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }

    public function resource_item()
    {
        return $this->hasMany(ResourceItem::class, 'category_id');
    }
}
