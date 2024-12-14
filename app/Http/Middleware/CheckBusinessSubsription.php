<?php

namespace App\Http\Middleware;

use App\Models\BusinessUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessSubsription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        $business_users = BusinessUser::where('user_id', $user->id)->with(relations: ['business', 'user'])->get();

        if ($business_users->isEmpty() || $business_users->contains(function ($businessUser) {
            return !$businessUser->business;
        })) {
            $businessTypes = DB::table("business_types")->get(["id", "name"]);
            $industries = DB::table("industries")->get(['id', 'name']);
            return redirect()->route('register-business')->with([
                "user" => $user,
                "businessTypes" => $businessTypes,
                "industries" => $industries,
            ]);
        }

        foreach ($business_users as $business_user) {
            if ($business_user->business && !$business_user->business->subscribed('default')) {
                return redirect()->route('choose-plan')->with([
                    "business" => $business_user->business,
                ]);
            }
        }
        return $next($request);
    }
}
