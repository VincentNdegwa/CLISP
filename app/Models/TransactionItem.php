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
        'item_id',
        'inventory_id',
        'inventory_batch_id',
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

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }
    
    public function inventoryBatch()
    {
        return $this->belongsTo(InventoryBatch::class, 'inventory_batch_id');
    }
}
