<?php

namespace App\Http\Middleware;

use App\Models\BusinessUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Check if the user is authenticated
        $user = Auth::user();
        $user_businesses = null;
        $role = null;

        if ($user) {
            $user_id = $user->id;
            $user_businesses = User::where('id', $user_id)->with([
                'business_user' => function ($query) {
                    $query->with(['business']);
                }
            ])->first();

            $default_business = $user_businesses->business_user->first()->business ?? null;
 
            if ($default_business) {
                $user_businesses->default_business = $user_businesses->defaultBusiness();
                $user_businesses->default_business->activeSubscription = $user_businesses->defaultBusiness()->subscribed('default');
    
                $role = BusinessUser::where('user_id', $user->id)
                    ->where('business_id', $default_business->business_id)
                    ->first();
            }
        }


        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            "user_businesses" => $user_businesses,
            "role" => $role
        ];
    }
}
