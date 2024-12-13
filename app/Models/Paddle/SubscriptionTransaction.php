<?php

namespace App\Models\Paddle;

use Laravel\Paddle\Transaction as BaseTransaction;

class SubscriptionTransaction extends BaseTransaction
{
    protected $table = 'subscription_transactions';
}
