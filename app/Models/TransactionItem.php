<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;
    protected $table = 'transaction_items';

    protected $fillable = [
        'transaction_id',
        "item_id",
        'quantity',
        'price',
        'status',

    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function item()
    {
        return $this->belongsTo(ResourceItem::class, 'item_id');
    }
}
