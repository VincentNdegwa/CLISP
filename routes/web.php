<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Paddle\PaddleDisplayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResourceCategoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {

    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome/Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
Route::get('/register-business', function () {
    return Inertia::render('Auth/RegisterBusiness', [
        "user" => session('user'),
        "businessTypes" => session('businessTypes'),
        "industries" => session('industries'),
    ]);
})->name('register-business');

Route::get('/choose-plan', function () {
    return Inertia::render('Auth/ChoosePlan', [
        "business" => session('business'),
    ]);
})->name('choose-plan');

Route::middleware(['auth', 'verified', 'check.business'])->group(function () {



    Route::get('/dashboard', function () {
        $user = Auth::user();
        $business_users = BusinessUser::where('user_id', $user->id)->with(['business', 'user'])->get();

        return Inertia::render('Dashboard/Main', [
            "business_id" => $business_users->first()->business->business_id ?? null
        ]);
    })->name('dashboard');

    Route::prefix('/dash')->group(function () {
        Route::post('/details', [DashboardController::class, 'create'])->name("dashboard.details");
    });
});



Route::middleware(['auth', 'check.business'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('/')->group(function () {
        Route::prefix("inventory")->group(function () {
            Route::get('/resources', [InventoryController::class, 'view'])->name('inventory.resources');
            Route::get('/resources/{id}', [ResourceCategoryController::class, 'openItem'])->name('inventory.item.view');
            Route::get('/categories', [ResourceCategoryController::class, 'view'])->name('inventory.categories');
        });

        Route::prefix("business")->group(function () {
            Route::get('/my-business', function () {
                return Inertia::render('Business/MyBusiness');
            })->name('business.my-business');
            // Route::get('/my-business/{id}', [ResourceCategoryController::class, 'viewSingleBusiness'])->name('business.item.view');
            Route::get('/connections', function () {
                return Inertia::render('Business/BusinessConnection');
            })->name('business.connection');
        });

        Route::prefix('trade-business')->name('b2b.')->group(function () {
            Route::get('/purchase', function () {
                return Inertia::render('Trade/Trade', [
                    'transactionType' => 'purchase',
                    'isB2B' => true
                ]);
            })->name('purchase');
            Route::get('/leasing', function () {
                return Inertia::render('Trade/Trade', [
                    'transactionType' => 'leasing',
                    'isB2B' => true
                ]);
            })->name('leasing');
            Route::get('/borrowing', function () {
                return Inertia::render('Trade/Trade', [
                    'transactionType' => 'borrowing',
                    'isB2B' => true
                ]);
            })->name('borrowing');
        });

        Route::prefix('trade-customer')->name('b2c.')->group(function () {
            Route::get('/sale', function () {
                return Inertia::render('Trade/Trade', [
                    'transactionType' => 'sale',
                    'isB2B' => false
                ]);
            })->name('sale');
            Route::get('/leasing', function () {
                return Inertia::render('Trade/Trade', [
                    'transactionType' => 'leasing',
                    'isB2B' => false
                ]);
            })->name('leasing');
            Route::get('/borrowing', function () {
                return Inertia::render('Trade/Trade', [
                    'transactionType' => 'borrowing',
                    'isB2B' => false
                ]);
            })->name('borrowing');
        });
        Route::prefix('transaction')->group(function () {
            Route::get('/view/{transaction_id}', function ($id) {
                return Inertia::render("Trade/ViewTransaction", [
                    'transactionId' => $id
                ]);
            })->name('transaction.view');
            Route::get('/view-agreement/{transaction_id}', [TransactionController::class, 'previewAgreement']);
            Route::get('/view-agreement/print/{transaction_id}', [TransactionController::class, 'printPreviewAgreement']);
            Route::get('/download-agreement/{transaction_id}', [TransactionController::class, 'downloadAgreement']);
            Route::get('/pdf-preview/{transaction_id}', [TransactionController::class, 'pdfPreviewAgreement']);

            Route::get('/view-receipt/print/{transaction_id}/{business_id}', [TransactionController::class, 'printPreviewReceipt']);
        });
    });

    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/my-customers', function () {
            return Inertia::render('Customers/MyCustomers');
        })->name('my-customers');
    });

    Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::get('shipmets', function () {
            return Inertia::render('Logistics/Shipments');
        })->name('shipments');
    });

    Route::get('not-found', function () {
        return Inertia::render('NotFound');
    })->name('not-found');


});


Route::get('billing', [PaddleDisplayController::class, 'show']);
Route::get("checkout", [PaddleDisplayController::class, 'subscribe']);


require __DIR__ . '/auth.php';
