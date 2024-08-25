<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
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
    if (!$user->busines_id) {
        $business = DB::table("business_types")->get(["id", "name"]);
        $industries = DB::table("industries")->get(['id', 'name']);
        return Inertia::render('Auth/RegisterBusiness', [
            "user" => $user,
            "businessTypes" => $business,
            "industries" => $industries,
        ]);
    }

    return Inertia::render('Dashboard/Main', [
        "user" => $user
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('subscription', [SubscriptionController::class, 'create'])->name('view.subscriptions');


// Route::middleware('auth')->prefix('business')->group(function () {
//     Route::post('/create', [BusinessController::class, 'Create'])->name('business.create');
//     Route::post('/update', [BusinessController::class, 'Update'])->name('business.update');
//     Route::post('/delete', [BusinessController::class, 'Delete'])->name('business.delete');
// });



require __DIR__ . '/auth.php';
