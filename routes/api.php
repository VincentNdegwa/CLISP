<?php

use App\Http\Controllers\BusinessConnectionController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\BusinessPaymentsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileSystemController;
use App\Http\Controllers\LogisticController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\ResourceCategoryController;
use App\Http\Controllers\ResourceItemController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use App\Models\BusinessConnection;
use App\Models\ResourceItem;
use Illuminate\Support\Facades\Route;

Route::prefix('business')->group(function () {
    Route::post('/create', [BusinessController::class, 'Create'])->name('business.create');
    Route::post('/update', [BusinessController::class, 'Update'])->name('business.update');
    Route::post('/delete', [BusinessController::class, 'Delete'])->name('business.delete');
    Route::get('/details', [BusinessController::class, 'getDetails'])->name('business.details');
    Route::get("/{business_id}/payment-methods", [BusinessPaymentsController::class, 'getPaymentMethods']);
});

Route::prefix('dashboard/{business_id}')->group(function () {
    Route::post('details', [DashboardController::class, 'create']);
});

Route::prefix('subscription')->group(function () {
    Route::post('/make', [SubscriptionController::class, 'pay'])->name('subscription.pay');
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


Route::prefix("payments")->group(function () {
    Route::post("/record-payment", [PaymentsController::class, 'createPayment']);
});
Route::post('/paypal/create-order', [PayPalController::class, 'createOrder']);
Route::post('/paypal/capture-order/{orderId}', [PayPalController::class, 'captureOrder']);
