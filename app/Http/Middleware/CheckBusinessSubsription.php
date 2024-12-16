<?php

namespace App\Http\Middleware;

use App\Models\BusinessUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $id = Auth::id();
        $user = User::find($id);
        $business = $user->businesses();
        if ($business->count() < 1) {
            return redirect()->route('register-business');
        }
        $unsubscribedBusiness = $user->unSubscribedBusiness();
        Log::info('Unsubscribed ' . $unsubscribedBusiness);
        if (isset($unsubscribedBusiness)) {
            return redirect()->route('choose-plan')->with([
                "business" => $unsubscribedBusiness,
            ]);
        }
        return $next($request);
    }
}
