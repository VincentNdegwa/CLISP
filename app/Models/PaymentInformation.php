<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentInformation extends Model
{
    protected $table = "payment_information";
    protected $fillable = [
        'business_id',
        'payment_type',
        'payment_details'
    ];

    protected function casts(): array
    {
        return [
            'payment_details' => 'array',
        ];
    }
}
