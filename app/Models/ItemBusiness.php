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
        'source',
        'quantity',
        'purchase_price',
        'lease_price',
        'borrow_fee',
        'tax_type',
        'tax_rate',

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
    public function transactionItemsHistory()
    {
        return $this->hasMany(TransactionItemHistory::class, 'item_business_id');
    }
}
