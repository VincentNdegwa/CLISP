<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
    protected $table = 'business';
    protected $fillable = [
        'business_name',
        'business_type',
        'location',
        'phone_number',
        'email',
        'website',
        'industry',
        'registration_number',
        'logo',
        'date_registered',
        'trust_score',
        'status',
        'subscription_plan',
    ];
}
