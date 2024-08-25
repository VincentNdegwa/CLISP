<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix('business')->group(function () {
    Route::post('/create', [BusinessController::class, 'Create'])->name('business.create');
    Route::post('/update', [BusinessController::class, 'Update'])->name('business.update');
    Route::post('/delete', [BusinessController::class, 'Delete'])->name('business.delete');
});

Route::prefix('subscription')->group(function () {
    Route::post('/make', [SubscriptionController::class, 'pay'])->name('subscription.pay');
});
Route::get("/test", function () {
    return ["hello" => '009'];
});
