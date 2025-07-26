<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessSubscriptionController;
use App\Http\Controllers\BusinessSubsriptionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\Paddle\PaddleDisplayController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResourceCategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\WarehouseController;
use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\SubscriptionPlan;
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

// Legal pages for domain review compliance
Route::get('/legal', function () {
    $tab = request('tab', 'terms');
    return Inertia::render('Welcome/Legal', [
        'activeTab' => $tab
    ]);
})->name('legal');

// Acceptable Use Policy page
Route::get('/acceptable-use', function () {
    return Inertia::render('Welcome/AcceptableUse');
})->name('acceptable-use');

// Security page
Route::get('/security', function () {
    return Inertia::render('Welcome/Security');
})->name('security');

Route::get('/default-business', function () {

    return  User::where('id', Auth::user()->id)->first()->defaultBusiness();
});

Route::get('/choose-plan', function () {
    $id = Auth::id();
    $business = User::find($id)->unSubscribedBusiness();
    $subscription_plans = SubscriptionPlan::all()
        ->groupBy('product_id')
        ->map(function ($plans, $product_id) {
            return $plans->map(function ($plan) {
                $plan->features = is_string($plan->features) ? json_decode($plan->features) : $plan->features;
                return $plan;
            });
        })
        ->values();


    return Inertia::render('Auth/ChoosePlan', [
        "business" => $business,
        'plans_t' => $subscription_plans,
    ]);
})->name('choose-plan');

Route::get('/billing', [BusinessSubscriptionController::class, 'index'])->name('billing.view');

Route::get("checkout/subscription/{business_id}/{price_id}", [PaddleDisplayController::class, 'choose'])->name('checkout.subscription');
Route::get("subscription/check/{business_id}", [PaddleDisplayController::class, 'checkSubscription'])->name('subscription.check');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/register-business', [BusinessController::class, 'openRegister'])->name('register-business');

});

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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix("business")->group(function () {
        Route::get('/my-business', function () {
            return Inertia::render('Business/MyBusiness');
        })->name('business.my-business');
        // Route::get('/my-business/{id}', [ResourceCategoryController::class, 'viewSingleBusiness'])->name('business.item.view');
        Route::get('/connections', function () {
            return Inertia::render('Business/BusinessConnection');
        })->name('business.connection');
    });
    Route::prefix("inventory")->group(function () {
        Route::get('/resources', [InventoryController::class, 'view'])->name('inventory.resources');
        Route::get('/resources/{id}', [ResourceCategoryController::class, 'openItem'])->name('inventory.item.view');
        Route::get('/categories', [ResourceCategoryController::class, 'view'])->name('inventory.categories');
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

    Route::prefix('purchasing')->name('purchasing.')->group(function () {
        // Purchase Orders - Views Only
        Route::get('/orders', [App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('orders');
        Route::get('/orders/create', [App\Http\Controllers\PurchaseOrderController::class, 'create'])->name('orders.create');
        Route::get('/orders/{purchaseOrder}', [App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{purchaseOrder}/edit', [App\Http\Controllers\PurchaseOrderController::class, 'edit'])->name('orders.edit');

        // Suppliers - Views Only
        Route::get('/suppliers', [App\Http\Controllers\SupplierController::class, 'index'])->name('suppliers');
        Route::get('/suppliers/create', [App\Http\Controllers\SupplierController::class, 'create'])->name('suppliers.create');
        Route::get('/suppliers/{supplier}', [App\Http\Controllers\SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('/suppliers/{supplier}/edit', [App\Http\Controllers\SupplierController::class, 'edit'])->name('suppliers.edit');

        // Goods Receipts - Views Only
        Route::get('/receipts', [App\Http\Controllers\GoodsReceiptController::class, 'index'])->name('receipts');
        Route::get('/receipts/create', [App\Http\Controllers\GoodsReceiptController::class, 'create'])->name('receipts.create');
        Route::get('/receipts/{goodsReceipt}', [App\Http\Controllers\GoodsReceiptController::class, 'show'])->name('receipts.show');
    });

    Route::prefix('sales')->name('sales.')->group(function () {
        // Sales Orders - Views Only
        Route::get('/orders', [App\Http\Controllers\SalesOrderController::class, 'index'])->name('orders');
        Route::get('/orders/create', [App\Http\Controllers\SalesOrderController::class, 'create'])->name('orders.create');
        Route::get('/orders/{salesOrder}', [App\Http\Controllers\SalesOrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{salesOrder}/edit', [App\Http\Controllers\SalesOrderController::class, 'edit'])->name('orders.edit');

        // Customers - Views Only
        Route::get('/customers', [App\Http\Controllers\CustomerController::class, 'index'])->name('customers');
        Route::get('/customers/create', [App\Http\Controllers\CustomerController::class, 'create'])->name('customers.create');
        Route::get('/customers/{customer}', [App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/edit', [App\Http\Controllers\CustomerController::class, 'edit'])->name('customers.edit');
        
        // Shipments - Views Only
        Route::get('/shipments', [App\Http\Controllers\ShipmentController::class, 'salesIndex'])->name('shipments');
        Route::get('/shipments/create', [App\Http\Controllers\ShipmentController::class, 'salesCreate'])->name('shipments.create');
        Route::get('/shipments/{shipment}', [App\Http\Controllers\ShipmentController::class, 'salesShow'])->name('shipments.show');
    });

    Route::prefix('warehouse')->name('warehouse.')->group(function () {
        // Goods Receipts - Views Only (already defined in purchasing)

        // Shipments - Views Only
        Route::get('/shipments', [App\Http\Controllers\ShipmentController::class, 'index'])->name('shipments');
        Route::get('/shipments/create', [App\Http\Controllers\ShipmentController::class, 'create'])->name('shipments.create');
        Route::get('/shipments/{shipment}', [App\Http\Controllers\ShipmentController::class, 'show'])->name('shipments.show');

        // Stock Movements - Views Only
        Route::get('/movements', [App\Http\Controllers\StockMovementController::class, 'index'])->name('movements');
        Route::get('/movements/create', [App\Http\Controllers\StockMovementController::class, 'create'])->name('movements.create');
        Route::get('/movements/{stockMovement}', [App\Http\Controllers\StockMovementController::class, 'show'])->name('movements.show');

        // Stock Counts - Views Only
        Route::get('/counts', [App\Http\Controllers\StockCountController::class, 'index'])->name('counts');
        Route::get('/counts/create', [App\Http\Controllers\StockCountController::class, 'create'])->name('counts.create');
        Route::get('/counts/{stockCount}', [App\Http\Controllers\StockCountController::class, 'show'])->name('counts.show');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [App\Http\Controllers\InventoryController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\InventoryController::class, 'create'])->name('create');
        Route::get('/{inventory}', [App\Http\Controllers\InventoryController::class, 'show'])->name('show');
        Route::get('/{inventory}/edit', [App\Http\Controllers\InventoryController::class, 'edit'])->name('edit');
        
        // Inventory Adjustments - Views Only
        Route::get('/adjustments', [App\Http\Controllers\InventoryAdjustmentController::class, 'index'])->name('adjustments');
        Route::get('/adjustments/create', [App\Http\Controllers\InventoryAdjustmentController::class, 'create'])->name('adjustments.create');
        Route::get('/adjustments/{adjustment}', [App\Http\Controllers\InventoryAdjustmentController::class, 'show'])->name('adjustments.show');
    });

    Route::prefix('customer')->name('customer.')->group(function () {
        Route::get('/my-customers', function () {
            return Inertia::render('Customers/MyCustomers');
        })->name('my-customers');
    });

    Route::prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('warehouses', function () {
            return Inertia::render('Warehouse/Warehouse/Index');
        })->name('warehouses');
        Route::get('warehouses/{id}', [WarehouseController::class, 'view'])->name('view');

        Route::get('bin-locations', function () {
            return Inertia::render('Warehouse/BinLocation/Index');
        })->name('bin-locations');
        Route::get('zones', function () {
            return Inertia::render('Warehouse/WarehouseZone/Index');
        })->name('zones');
    });
    Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::get('shipments', function () {
            return Inertia::render('Logistics/Shipments');
        })->name('shipments');

        Route::get('carriers', function () {
            return Inertia::render('Logistics/Carriers');
        })->name('carriers');
    });

    Route::get('settings', function () {
        return Inertia::render('Settings/Settings');
    })->name('settings.view');
});
Route::get('not-found', function () {
    return Inertia::render('NotFound');
})->name('not-found');

require __DIR__ . '/auth.php';
