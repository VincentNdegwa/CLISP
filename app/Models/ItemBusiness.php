<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemBusiness extends Model
{
    use HasFactory;
    protected $fillable = [
        'item_id',
        'business_id',
        'source'

    ];
    protected $table = 'item_business';

    public function items()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id', 'id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }
}
