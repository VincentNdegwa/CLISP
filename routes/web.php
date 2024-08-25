<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {

    return Inertia::render('Welcome/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $user = Auth()->user();
    $business_users = BusinessUser::where('user_id', $user->id)->with(['business', 'user'])->get();

    // If the user is not associated with any business or the business is missing
    if ($business_users->isEmpty() || $business_users->contains(function ($businessUser) {
        return !$businessUser->business;
    })) {
        $businessTypes = DB::table("business_types")->get(["id", "name"]);
        $industries = DB::table("industries")->get(['id', 'name']);

        return Inertia::render('Auth/RegisterBusiness', [
            "user" => $user,
            "businessTypes" => $businessTypes,
            "industries" => $industries,
        ]);
    }

    // Check if any business is missing a subscription plan
    foreach ($business_users as $business_user) {
        if ($business_user->business && !$business_user->business->subscription_plan) {
            return Inertia::render("Auth/ChoosePlan", [
                "business" => $business_user->business
            ]);
        }
    }

    // Render the main dashboard
    return Inertia::render('Dashboard/Main', [
        "user" => $user,
        "user_businesses" => User::where('id', $user->id)->with([
            "business_user" => function ($query) {
                $query->with(['business']);
            }
        ])->first()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route::get('subscription', [SubscriptionController::class, 'create'])->name('view.subscriptions');


// Route::middleware('auth')->prefix('business')->group(function () {
//     Route::post('/create', [BusinessController::class, 'Create'])->name('business.create');
//     Route::post('/update', [BusinessController::class, 'Update'])->name('business.update');
//     Route::post('/delete', [BusinessController::class, 'Delete'])->name('business.delete');
// });



require __DIR__ . '/auth.php';
