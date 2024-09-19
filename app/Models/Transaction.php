<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'transactions';

    protected $fillable = [
        "type",
        "status",
        "initiator_id",
        "receiver_business_id",
        "receiver_customer_id",
        "lease_start_date",
        "lease_end_date",
        // "idDeleted"
    ];

    public function details()
    {
        return $this->hasOne(TransactionDetail::class, 'transaction_id');
    }

    public function initiator()
    {
        return $this->belongsTo(Business::class, 'initiator_id', 'business_id');
    }

    public function receiver_business()
    {
        return $this->belongsTo(Business::class, 'receiver_business_id', 'business_id');
    }

    public function receiver_customer()
    {
        return $this->belongsTo(Customer::class, 'receiver_customer_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }
}
