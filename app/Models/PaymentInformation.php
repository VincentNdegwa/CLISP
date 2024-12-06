<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentInformation extends Model
{
    protected $table = "payment_informations";
    protected $fillable = [
        'business_id',
        'payment_type',
        'payment_details'
    ];

    protected $casts = [
        'payment_details' => 'array',
    ];

  
}
