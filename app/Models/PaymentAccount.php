<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentAccount extends Model
{
    use HasFactory;
    protected $fillable = [
        'business_id',
        'account_type',
        'account_number',
        'account_holder',
        'expiration_date',
        'bank_name',
        'paypal_email',
        'is_active'
    ];
    protected $table = 'payment_accounts';

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'business_id');
    }
}
