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
        'business_type_id',
        'location',
        'phone_number',
        'email',
        'website',
        'industry_id',
        'registration_number',
        'logo',
        'date_registered',
        'trust_score',
        'status',
        'subscription_plan',
    ];

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }


    public function subscription()
    {
        return $this->belongsTo(Subscription::class, "subscription_plan");
    }
    public function business_user()
    {
        return $this->hasMany(BusinessUser::class, 'business_id');
    }
}
