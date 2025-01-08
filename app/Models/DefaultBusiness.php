<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultBusiness extends Model
{
    protected $table = 'default_business';

    protected $fillable = [
        'business_id',
        'user_id'
    ];
}
