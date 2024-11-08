<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;


    protected $table = 'payments';

    protected $fillable = [
        'payer_name',
        'payer_email',
        'payment_method',
        'payment_reference',
        'paid_amount',
        'transaction_id',
        'remaining_balance',
        'payer_id',
        'isB2B',
        'payee_business',
        'currency_code',
    ];


    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }


    public function payer()
    {
        if ($this->isb2b) {
            return $this->belongsTo(Business::class, 'payer_id', "business_id");
        } else {
            return $this->belongsTo(Customer::class, 'payer_id');
        }
    }


    public function payeeBusiness()
    {
        return $this->belongsTo(Business::class, 'payee_business', "business_id");
    }
}
