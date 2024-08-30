<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResourceCategoryController;
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

    foreach ($business_users as $business_user) {
        if ($business_user->business && !$business_user->business->subscription_plan) {
            return Inertia::render("Auth/ChoosePlan", [
                "business" => $business_user->business
            ]);
        }
    }
    return Inertia::render('Dashboard/Main', [
        "business_id" => $business_users->first()->business->business_id ?? null
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('/dash')->group(function () {
    Route::post('/details', [DashboardController::class, 'create'])->name("dashboard.details");
});


Route::middleware('auth')->prefix('/')->group(function () {
    Route::prefix("inventory")->group(function () {
        Route::get('/resources', [InventoryController::class, 'view'])->name('inventory.resources');
        Route::get('/categories', [ResourceCategoryController::class, 'view'])->name('inventory.categories');
    });
});




require __DIR__ . '/auth.php';
