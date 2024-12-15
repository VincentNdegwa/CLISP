<?php

namespace App\Http\Middleware;

use App\Models\BusinessUser;
use App\Models\User;
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
        $id = Auth::id();
        $user = User::find($id);
        $business = $user->unSubscribedBusiness();
        if (isset($business)) {
            return redirect()->route('choose-plan')->with([
                "business" => $business,
            ]);
        }
        return $next($request);
    }
}
