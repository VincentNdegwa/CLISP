<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\Paddle\SubscriptionCustomer;
use App\Models\Paddle\SubscriptionTransaction;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Paddle\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useTransactionModel(SubscriptionTransaction::class);
        Cashier::useCustomerModel(SubscriptionCustomer::class);

        // if (App::environment('production')) {
        //     URL::forceScheme('https');
        // }
    }
}
