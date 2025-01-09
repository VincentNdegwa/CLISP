<?php

namespace App\Http\Controllers\Paddle;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class PaddleDisplayController extends Controller
{
    public function show(Request $request)
    {
        return view('Paddle/billing');
    }

    public function activated(Request $request)
    {
        return view('paddle.subscription-activated');
    }


    public function cancelled(Request $request)
    {
        return view('paddle.subscription-cancelled');
    }


    public function choose($business_id, $price_id, Request $request)
    {
        $user = Auth::user();

        $userExistInBusiness = BusinessUser::where('business_id', $business_id)->where('user_id', $user->id)->exists();

        if (!$userExistInBusiness) {
            return redirect(route('dashboard'));
        }

        $business = Business::where('business_id', $business_id)->first();

        $subscription_plan = SubscriptionPlan::where('price_id', $price_id)->first();
        if ($business && $business->subscribed('default')) {

            return view('Paddle.checkout', ['payment_status' => "subscribed", 'business' => $business, 'subscription' => $subscription_plan, 'checkout' => null,]);
        }
        if ($subscription_plan) {
            $checkout = $business->subscribe(
                $priceId = $price_id,
                $type = 'default'
            )->returnTo(route('billing.view', ['business_id' => $business->business_id]));


            return view('Paddle.checkout', ['checkout' => $checkout, 'business' => $business, 'subscription' => $subscription_plan, 'payment_status' => null]);
        } else {
            return redirect(route('dashboard'));
        }
    }

    public function checkSubscription($business_id)
    {
        $business = Business::where('business_id', $business_id)->first();
        if ($business && $business->subscribed('default')) {
            return redirect(route('billing.view'));
        } else {
            return redirect(route(name: 'login'));
        }
    }
}
