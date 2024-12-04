<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = "payment_methods";
    protected $fillable = [
        'business_id',
        'name',
        'default',
        'icon',
        'category'
    ];
    //
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
