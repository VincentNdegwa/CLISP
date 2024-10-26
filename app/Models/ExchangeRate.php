<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;
    protected $fillable = [
        'target_currency',
        'rate',
        'last_update',
        'base_currency'
    ];

    protected $table= 'exchange_rates';
}
