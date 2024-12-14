<?php

namespace App\Http\Controllers\Paddle;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
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

    public function subscribe()
    {

        $price_id = Session::get('price_id');
        $user = Auth::user();
        $business_users = BusinessUser::where('user_id', $user->id)->with(['business', 'user'])->first();

        $business = Business::where('business_id', $business_users->business->business_id)->first();
        $checkout = $business->subscribe(
            $priceId = $price_id,
            $type = 'default'
        )->returnTo(route('dashboard'));

        return view('Paddle.checkout', ['checkout' => $checkout]);
    }


    public function choose($price_id, Request $request)
    {
        Log::info("Redirecting to checkout with price_id: " . $price_id);


        return Redirect::to('/checkout')->with(['price_id' => $price_id]);
    }
}
