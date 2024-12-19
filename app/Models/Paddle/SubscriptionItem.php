<?php

namespace App\Models\Paddle;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Model;
use Laravel\Paddle\SubscriptionItem as PaddleSubscriptionItem;

class SubscriptionItem extends PaddleSubscriptionItem
{
    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'price_id', 'price_id');
    }
}
