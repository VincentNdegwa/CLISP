<?php

namespace App\Models\Paddle;

use Illuminate\Database\Eloquent\Model;
use Laravel\Paddle\Customer as BaseCustomer;

class SubscriptionCustomer extends BaseCustomer
{
    protected $table = "subscription_customers";
}
