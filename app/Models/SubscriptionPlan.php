<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'price_id',
        'name',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'features',
    ];

    protected $casts = [
        'features' => 'array',
    ];
}
