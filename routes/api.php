<?php

use App\Models\Business;
use App\Models\ResourceItem;
use App\Models\BusinessConnection;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LogisticController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\FileSystemController;
use App\Http\Controllers\BinLocationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ResourceItemController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\WarehouseZoneController;
use App\Http\Controllers\BusinessPaymentsController;
use App\Http\Controllers\ResourceCategoryController;
use App\Http\Controllers\BusinessConnectionController;
use App\Http\Controllers\StockMovementReasonController;
use App\Http\Controllers\BusinessSubscriptionController;
use App\Http\Controllers\Paddle\CustomWebhookController;
use App\Http\Controllers\Paddle\PaddleDisplayController;
use App\Http\Controllers\StockAdjustmentReasonController;

// Public routes that don't require authentication
Route::post('/business/create', [BusinessController::class, 'Create'])->name('business.create');
Route::prefix('subscription')->group(function () {
    Route::post('/make', [SubscriptionController::class, 'pay'])->name('subscription.pay');
});
Route::get('custom-webhook', [CustomWebhookController::class, '__invoke']);
Route::post('/paypal/create-order', [PayPalController::class, 'createOrder']);
Route::post('/paypal/capture-order/{orderId}', [PayPalController::class, 'captureOrder']);

Route::prefix('mobile')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\MobileAuthController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('mobile')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Api\MobileAuthController::class, 'logout']);
        Route::get('/profile', [App\Http\Controllers\Api\MobileAuthController::class, 'profile']);
        Route::post('/refresh-token', [App\Http\Controllers\Api\MobileAuthController::class, 'refreshToken']);
    });

    Route::prefix('business')->group(function () {
        Route::post('/update', [BusinessController::class, 'Update'])->name('business.update');
        Route::post('/delete', [BusinessController::class, 'Delete'])->name('business.delete');
        Route::get('/details', [BusinessController::class, 'getDetails'])->name('business.details');
        Route::get("/{business_id}/payment-methods", [BusinessPaymentsController::class, 'getPaymentMethods']);
        Route::post("/{business_id}/payment-information", [BusinessPaymentsController::class, "createOrUpdatePaymentInformation"]);
        Route::get("/{business_id}/payment-information", [BusinessPaymentsController::class, "getPaymentInformation"]);
        Route::post("/{business_id}/search-payment-information", [BusinessPaymentsController::class, "fetchSinglePaymentInformation"]);
        Route::post("/{business_id}/payment-information/default/{payment_id}", [BusinessPaymentsController::class, "setDefault"]);
        Route::get("/{business_id}/check-subscription", [PaddleDisplayController::class, 'checkSubscription']);
        Route::get("/{business_id}/{user_id}/set-default-business", [BusinessController::class, 'setDefaultBusiness']);
        Route::post("/{business_id}/change-plan", [BusinessController::class, 'changePlan']);
    });

    Route::prefix('dashboard/{business_id}')->group(function () {
        Route::post('details', [DashboardController::class, 'create']);
    });

    Route::prefix('file')->group(function () {
        Route::post('/upload', [FileSystemController::class, 'upload'])->name('file.upload');
        Route::post('/delete', [FileSystemController::class, 'delete'])->name('file.delete');
    });

    Route::prefix("item/{business_id}")->group(function () {
        Route::post("/create", [ResourceItemController::class, "create"]);
        Route::get("/list", [ResourceItemController::class, 'read']);
        Route::post("/update", [ResourceItemController::class, 'update']);
        Route::get('/resources/{id}', [ResourceItemController::class, 'getSingleResource']);
        Route::get('/inventory/{id}', [ResourceItemController::class, 'getInventoryByResource']);
    });
    Route::delete("item/delete/{id}", [ResourceItemController::class, 'delete']);
    Route::delete("category/delete/{id}", [ResourceCategoryController::class, 'delete']);

    Route::prefix("category/{business_id}")->group(function () {
        Route::post("/create", [ResourceCategoryController::class, "create"]);
        Route::get("/list", [ResourceCategoryController::class, "read"]);
        Route::post("/update", [ResourceCategoryController::class, "update"]);
    });

    Route::prefix("business")->group(function () {
        Route::post("/my-business", [BusinessController::class, "fetchMyBusiness"]);
        Route::get("/connection-requests/{business_id}", [BusinessConnectionController::class, "getBusinessConnection"]);
        Route::get("/search-business", [BusinessController::class, "getBusinessSearch"]);
        Route::post('/send-connection-request', [BusinessConnectionController::class, "sendConnectionRequest"]);
        Route::post('/approve-connection-request', [BusinessConnectionController::class, "approveConnectionRequest"]);
        Route::post('/reject-connection-request', [BusinessConnectionController::class, "rejectConnectionRequest"]);
        Route::post('/cancel-connection-request', [BusinessConnectionController::class, "cancelConnectionRequest"]);
        Route::post('/terminate-connection', [BusinessConnectionController::class, "terminateConnection"]);
        Route::get("/get-active-connection/{business_id}", [BusinessConnectionController::class, "getActiveConnections"]);
    });

    // Inventory and warehouse management
    Route::resource('inventory', InventoryController::class)->names('api.inventory');

    // Shipment routes
    Route::resource('shipments', ShipmentController::class);
    Route::post('shipments/{id}/mark-shipped', [ShipmentController::class, 'markAsShipped']);
    Route::post('shipments/{id}/mark-delivered', [ShipmentController::class, 'markAsDelivered']);

    // Warehouse routes
    Route::resource('warehouses', WarehouseController::class);

    // Bin Location routes
    Route::resource('bin-locations', BinLocationController::class);
    Route::get('bin-locations/by-warehouse/{warehouse_id}', [BinLocationController::class, 'getByWarehouse']);
    Route::apiResource('warehouse-zones', WarehouseZoneController::class);
    Route::get('zones-types', [WarehouseZoneController::class, 'getZoneTypes']);
    Route::get('warehouses/{warehouseId}/zones', [WarehouseZoneController::class, 'getZonesByWarehouse']);

    // Carrier routes
    Route::resource('carriers', CarrierController::class);

    // Stock Movement routes
    Route::resource('stock-movements', StockMovementController::class);
    Route::get('stock-movements/by-inventory/{inventory_id}', [StockMovementController::class, 'getByInventory']);

    // Additional inventory routes
    Route::post('inventory/{id}/adjust-quantity', [InventoryController::class, 'adjustQuantity']);
    Route::post('inventory/{id}/move', [InventoryController::class, 'moveInventory']);
    Route::get('inventory/low-stock', [InventoryController::class, 'getLowStock']);
    Route::get('inventory/summary', [InventoryController::class, 'getSummary']);
    Route::post('inventory/{inventoryId}/process-batch', [InventoryController::class, 'processBatch']);
    Route::get('inventory/{inventoryId}/batches', [InventoryController::class, 'getBatches']);

    // Stock adjustment reasons
    Route::resource('stock-adjustment-reasons', StockAdjustmentReasonController::class);

    // Stock movement reasons
    Route::resource('stock-movement-reasons', StockMovementReasonController::class);

    Route::prefix("customers")->group(function () {
        Route::post('/create-customer', [CustomerController::class, "create"]);
        Route::get('/business-customers/{business_id}', [CustomerController::class, "getBusinessCustomers"]);
        Route::patch('/update-customer', [CustomerController::class, "update"]);
        Route::delete('/delete-customer/{id}', [CustomerController::class, "delete"]);
    });

    Route::prefix('transactions/{business_id}')->group(function () {
        Route::post('/add-transaction', [TransactionController::class, "create"]);
        Route::post('/get-transaction', [TransactionController::class, 'getTransaction']);
        Route::patch('/update-transaction/{transaction_id}', [TransactionController::class, 'updateTransaction']);
        Route::delete('/delete-transaction/{transaction_id}', [TransactionController::class, 'deleteTransaction']);
        Route::post('/view/{transaction_id}', [TransactionController::class, "viewTransaction"]);

        Route::post('/accept-transaction/{transaction}', [TransactionController::class, 'acceptTransaction']);
        Route::post('/reject-transaction/{transaction}', [TransactionController::class, 'rejectTransaction']);
        Route::post('/accept-and-pay-transaction/{transaction}', [TransactionController::class, 'acceptAndPayTransaction']);
        Route::post('/pay-transaction/{transaction}', [TransactionController::class, 'payTransaction']);
        Route::post('/close-transaction/{transaction}', [TransactionController::class, 'closeTransaction']);

        Route::post('/logistics', [LogisticController::class, 'getLogistics']);

        Route::post('/logistics/dispatch-tems', [LogisticController::class, 'dispatchItems']);
        Route::post('/logistics/receive-items', [LogisticController::class, 'receiveItems']);
        Route::post('/logistics/return-items', [LogisticController::class, 'returnItems']);
        Route::post('/logistics/reject-items', [LogisticController::class, 'rejectItems']);
    });

    Route::prefix('{business_id}/billing')->group(function () {
        Route::get('/', [BusinessSubscriptionController::class, 'getBilling']);
        Route::get('/transaction', [BusinessSubscriptionController::class, 'getBillingTransactions']);
        Route::post('/cancel-subscription', [BusinessSubscriptionController::class, 'cancelSubscription']);
    });

    Route::prefix("payments")->group(function () {
        Route::post("/record-payment", [PaymentsController::class, 'createPayment']);
    });


    // Purchase Orders
    Route::prefix('purchase-orders')->group(function () {
        Route::get('/', [App\Http\Controllers\PurchaseOrderController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\PurchaseOrderController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'apiUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\PurchaseOrderController::class, 'apiDestroy']);
        Route::post('/{id}/cancel', [App\Http\Controllers\PurchaseOrderController::class, 'apiCancel']);
    });

    // Suppliers
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [App\Http\Controllers\SupplierController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\SupplierController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\SupplierController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\SupplierController::class, 'apiUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\SupplierController::class, 'apiDestroy']);
    });

    // Goods Receipts
    Route::prefix('goods-receipts')->group(function () {
        Route::get('/', [App\Http\Controllers\GoodsReceiptController::class, 'index']);
        Route::post('/', [App\Http\Controllers\GoodsReceiptController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\GoodsReceiptController::class, 'show']);
        Route::post('/{id}/inspect', [App\Http\Controllers\GoodsReceiptController::class, 'inspectItems']);
        Route::post('/{id}/complete', [App\Http\Controllers\GoodsReceiptController::class, 'complete']);
        Route::post('/{id}/create-inventory', [App\Http\Controllers\GoodsReceiptController::class, 'apiCreateInventory']);
        Route::get('/purchase-order-items', [App\Http\Controllers\GoodsReceiptController::class, 'getPurchaseOrderItems']);
    });

    // Sales Orders
    Route::prefix('sales-orders')->group(function () {
        Route::get('/', [App\Http\Controllers\SalesOrderController::class, 'index']);
        Route::post('/', [App\Http\Controllers\SalesOrderController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\SalesOrderController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\SalesOrderController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\SalesOrderController::class, 'destroy']);
        Route::post('/{id}/confirm', [App\Http\Controllers\SalesOrderController::class, 'confirm']);
        Route::post('/{id}/process', [App\Http\Controllers\SalesOrderController::class, 'process']);
        Route::post('/{id}/cancel', [App\Http\Controllers\SalesOrderController::class, 'cancel']);
        Route::post('/{id}/allocate', [App\Http\Controllers\SalesOrderController::class, 'allocateInventory']);
    });

    // Shipments
    Route::prefix('shipments')->group(function () {
        Route::get('/', [App\Http\Controllers\ShipmentController::class, 'index']);
        Route::post('/', [App\Http\Controllers\ShipmentController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\ShipmentController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\ShipmentController::class, 'update']);
        Route::post('/{id}/ship', [App\Http\Controllers\ShipmentController::class, 'markAsShipped']);
        Route::post('/{id}/deliver', [App\Http\Controllers\ShipmentController::class, 'markAsDelivered']);
    });

    // Stock Movements
    Route::prefix('stock-movements')->group(function () {
        Route::get('/', [App\Http\Controllers\StockMovementController::class, 'index']);
        Route::post('/', [App\Http\Controllers\StockMovementController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\StockMovementController::class, 'show']);
    });

    // Stock Counts
    Route::prefix('stock-counts')->group(function () {
        Route::get('/', [App\Http\Controllers\StockCountController::class, 'index']);
        Route::post('/', [App\Http\Controllers\StockCountController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\StockCountController::class, 'show']);
        Route::post('/{id}/update-counts', [App\Http\Controllers\StockCountController::class, 'updateCounts']);
        Route::post('/{id}/verify', [App\Http\Controllers\StockCountController::class, 'verify']);
        Route::post('/{id}/adjust', [App\Http\Controllers\StockCountController::class, 'createAdjustment']);
    });

    // Inventory Adjustments
    Route::prefix('inventory-adjustments')->group(function () {
        Route::get('/', [App\Http\Controllers\InventoryAdjustmentController::class, 'apiIndex']);
        Route::post('/', [App\Http\Controllers\InventoryAdjustmentController::class, 'apiStore']);
        Route::get('/{id}', [App\Http\Controllers\InventoryAdjustmentController::class, 'apiShow']);
        Route::put('/{id}', [App\Http\Controllers\InventoryAdjustmentController::class, 'apiUpdate']);
        Route::delete('/{id}', [App\Http\Controllers\InventoryAdjustmentController::class, 'apiDestroy']);
        Route::post('/{id}/approve', [App\Http\Controllers\InventoryAdjustmentController::class, 'apiApprove']);
    });

    // Inventory
    Route::prefix('inventory')->group(function () {
        Route::get('/', [App\Http\Controllers\InventoryController::class, 'apiIndex']);
        // The following methods likely don't exist with api prefix
        Route::post('/', [App\Http\Controllers\InventoryController::class, 'store']);
        Route::get('/{id}', [App\Http\Controllers\InventoryController::class, 'show']);
        Route::put('/{id}', [App\Http\Controllers\InventoryController::class, 'update']);
        Route::delete('/{id}', [App\Http\Controllers\InventoryController::class, 'destroy']);
        Route::post('/{id}/adjust-quantity', [App\Http\Controllers\InventoryController::class, 'adjustQuantity']);
        Route::post('/{id}/move', [App\Http\Controllers\InventoryController::class, 'moveInventory']);
        Route::get('/low-stock', [App\Http\Controllers\InventoryController::class, 'getLowStock']);
        Route::get('/summary', [App\Http\Controllers\InventoryController::class, 'getSummary']);
        Route::post('/{inventoryId}/process-batch', [App\Http\Controllers\InventoryController::class, 'processBatch']);
        Route::get('/{inventoryId}/batches', [App\Http\Controllers\InventoryController::class, 'getBatches']);
    });
});
