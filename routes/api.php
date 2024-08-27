<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\FileSystemController;
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
Route::prefix('file')->group(function () {
    Route::post('/upload', [FileSystemController::class, 'upload'])->name('file.upload');
    Route::post('/delete', [FileSystemController::class, 'delete'])->name('file.delete');
});
