<?php

namespace App\Http\Controllers\Paddle;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaddleDisplayController extends Controller
{
    public function show(Request $request){
        return view('Paddle/billing');
    }

    public function subscribe(Request $request){
        $user = Auth::user();
        $business_users = BusinessUser::where('user_id', $user->id)->with(relations: ['business', 'user'])->first();

        $business = Business::where('business_id', $business_users->business->business_id)->first();
           $checkout = $business->subscribe(
            $priceId = 'pri_01jf0v01jhed7prk0snspzhttn',
            $type = 'default'
        )->returnTo(route('dashboard'));

        return view('Paddle.checkout', ['checkout'=> $checkout]);
    }
}
