<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
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
        $user_businesses = User::where('id', Auth()->user()->id)->with([
            'business_user' => function ($query) {
                $query->with(['business']);
            }
        ])->first();
        $default_business = $user_businesses->business_user->first()->business ?? null;

        $user_businesses->default_business = $default_business;
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            "user_businesses" => $user_businesses
        ];
    }
}
