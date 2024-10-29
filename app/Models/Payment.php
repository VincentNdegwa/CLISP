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
        'payer_business',
        'payee_business',
        'currency_code',
    ];


    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }


    public function payerBusiness()
    {
        return $this->belongsTo(Business::class, 'payer_business', "business_id");
    }


    public function payeeBusiness()
    {
        return $this->belongsTo(Business::class, 'payee_business', "business_id");
    }
}
