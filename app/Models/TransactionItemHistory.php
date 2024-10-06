<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItemHistory extends Model
{
    use HasFactory;
    protected $table = 'transaction_items_history';
    protected $fillable = [
        'item_business_id',
        'transaction_type',
        'quantity',
        'transaction_time',
    ];

    public function itemBusiness()
    {
        return $this->belongsTo(ItemBusiness::class, 'item_business_id');
    }
}
