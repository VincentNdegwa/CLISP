<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\ItemBusiness;
use App\Models\Transaction;
use App\Models\ResourceItem;
use App\Models\Subscription;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function create(Request $request, $business_id)
    {
        try {


            // Key Metrics

            $totalRevenue = TransactionItem::with([
                'transaction' => function ($query) use ($business_id) {
                    $query->where('initiator_id', $business_id)
                        ->where(
                            'created_at',
                            '>=',
                            Carbon::now()->subMonth()
                        );
                }
            ])->sum('price');


            $newCustomers = Customer::where('created_at', '>=', Carbon::now()->subMonth())
                ->where('business_id', $business_id)
                ->count();

            $sellingTransactionsByType = Transaction::select('type', DB::raw('count(*) as total'))
                ->where('initiator_id', $business_id)
                ->groupBy('type')
                ->get();

            $buyingTransactionsByType = Transaction::select('type', DB::raw('count(*) as total'))
                ->where('receiver_business_id', $business_id)
                ->groupBy('type')
                ->get();

            $lowStockItems = ItemBusiness::where('business_id', $business_id)
                ->where('quantity', '<', 5)
                ->get();

            $revenueTrends = TransactionItem::select(
                DB::raw('DATE_FORMAT(transaction_items.created_at, "%Y-%m") as month'),
                DB::raw('SUM(transaction_items.price * transaction_items.quantity) as total_revenue')
            )
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            $revenueByType = TransactionItem::select(
                DB::raw('SUM(transaction_items.price * transaction_items.quantity) as revenue'),
                'transactions.type'
            )
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->groupBy('transactions.type')
                ->get();

            $dashboardData = [
                'totalRevenue' => $totalRevenue,
                'newCustomers' => $newCustomers,
                'sellingTransactionsByType' => $sellingTransactionsByType,
                'buyingTransactionsByType' => $buyingTransactionsByType,
                'lowstockItems' => $lowStockItems,
                'revenueTrends' => $revenueTrends,
                'revenueByType' => $revenueByType,

            ];

            // Render the Inertia view with dashboard data

            return response()->json([
                'error' => false,
                'dashboardData' => $dashboardData,


            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ]);
        }
    }
}
