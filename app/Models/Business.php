<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Paddle\Billable;


class Business extends Model
{

    use HasFactory, Billable;
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
        'currency_code'
    ];
    protected $primaryKey = 'business_id';

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class, 'industry_id');
    }

    public function business_user()
    {
        return $this->hasMany(BusinessUser::class, 'business_id');
    }

    public function resource_category()
    {
        return $this->hasMany(ResourceCategory::class, 'business_id', 'business_id');
    }

    public function business_item()
    {
        return $this->hasMany(ItemBusiness::class, 'business_id');
    }

    public static function setDefaultBusiness($business_id, $user_id)
    {
        DefaultBusiness::updateOrCreate(
            [
                'user_id' => $user_id
            ],
            [
                'business_id' => $business_id,
                'user_id' => $user_id
            ]
        );
    }
    
}
