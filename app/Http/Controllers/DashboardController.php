<?php

namespace App\Http\Controllers;

use App\Models\BusinessConnection;
use App\Models\ItemBusiness;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function create(Request $request, $business_id)
    {
        try {
            $transactionTrends = ['Incomming', 'Outgoing'];

            $statusToRevenue = ['paid', 'partially-dispatched', 'dispatched', 'completed'];

            function calculatePercentageDifference($current, $previous)
            {
                if ($previous == 0) {
                    return $current > 0 ? 100 : 0;
                }
                return (($current - $previous) / $previous) * 100;
            }

            $todayRevenueSummary = [];
            $todayMonthRevenueSummary = [];
            $todayYearRevenueSummary = [];

            // Today's Revenue
            $todayRevenue = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->whereIn('transactions.status', $statusToRevenue)
                ->whereDate('transactions.created_at', Carbon::today())
                ->sum(DB::raw('price * quantity'));

            // Yesterday's Revenue
            $yesterdayRevenue = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->whereIn('transactions.status', $statusToRevenue)
                ->whereDate('transactions.created_at', Carbon::yesterday())
                ->sum(DB::raw('price * quantity'));

            // Percentage Difference for Today's Revenue
            $todayRevenueDifference = calculatePercentageDifference($todayRevenue, $yesterdayRevenue);

            $todayRevenueSummary['revenue'] = $todayRevenue;
            $todayRevenueSummary['difference'] = $todayRevenueDifference;
            $todayRevenueSummary['previous_revenue'] = $yesterdayRevenue;
            $todayRevenueSummary['title'] = "Today's Revenue";

            // This Month's Revenue
            $monthRevenue = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->whereIn('transactions.status', $statusToRevenue)
                ->whereYear('transactions.created_at', Carbon::now()->year)
                ->whereMonth('transactions.created_at', Carbon::now()->month)
                ->sum(DB::raw('price * quantity'));

            // Last Month's Revenue
            $lastMonthRevenue = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->whereIn('transactions.status', $statusToRevenue)
                ->whereYear('transactions.created_at', Carbon::now()->subMonth()->year)
                ->whereMonth('transactions.created_at', Carbon::now()->subMonth()->month)
                ->sum(DB::raw('price * quantity'));

            // Percentage Difference for This Month's Revenue
            $monthRevenueDifference = calculatePercentageDifference($monthRevenue, $lastMonthRevenue);

            $todayMonthRevenueSummary['revenue'] = $monthRevenue;
            $todayMonthRevenueSummary['difference'] = $monthRevenueDifference;
            $todayMonthRevenueSummary['previous_revenue'] = $lastMonthRevenue;
            $todayMonthRevenueSummary['title'] = "This Month's Revenue";

            // This Year's Revenue
            $yearRevenue = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->whereIn('transactions.status', $statusToRevenue)
                ->whereYear('transactions.created_at', Carbon::now()->year)
                ->sum(DB::raw('price * quantity'));

            // Last Year's Revenue
            $lastYearRevenue = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->whereIn('transactions.status', $statusToRevenue)
                ->whereYear('transactions.created_at', Carbon::now()->subYear()->year)
                ->sum(DB::raw('price * quantity'));

            // Percentage Difference for This Year's Revenue
            $yearRevenueDifference = calculatePercentageDifference($yearRevenue, $lastYearRevenue);

            $todayYearRevenueSummary['revenue'] = $yearRevenue;
            $todayYearRevenueSummary['difference'] = $yearRevenueDifference;
            $todayYearRevenueSummary['previous_revenue'] = $lastYearRevenue;
            $todayYearRevenueSummary['title'] = "This Year's Revenue";

            // Active Business Connections
            $businessSummary = [];
            $activeConnections = BusinessConnection::where(function ($query) use ($business_id) {
                $query->where('requesting_business_id', $business_id)
                    ->orWhere('receiving_business_id', $business_id);
            })
                ->where('connection_status', 'approved')
                ->count();

            // Total Items and Total Items Value
            $totalItems = ItemBusiness::where('business_id', $business_id)->whereHas("items")->count();

            $totalItemsValue = ItemBusiness::join('resource_item', 'item_business.item_id', '=', 'resource_item.id')
                ->where('item_business.business_id', $business_id)
                ->sum(DB::raw('item_business.quantity * resource_item.price'));
            $businessSummary['businessConnection'] = $activeConnections;
            $businessSummary['totalItems'] = $totalItems;
            $businessSummary['totalItemsValue'] = $totalItemsValue;

            // Low Stock Items
            $lowStockItems = ItemBusiness::where('business_id', $business_id)
                ->where('quantity', '<', 5)
                ->get();

            // Revenue Trends (Month Wise)
            $revenueTrends = TransactionItem::select(
                DB::raw('DATE_FORMAT(transactions.created_at, "%M") as month'),
                DB::raw('MONTH(transactions.created_at) as month_number'),
                DB::raw('SUM(transaction_items.price * transaction_items.quantity) as total_revenue')
            )
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->whereIn('transactions.status', $statusToRevenue)
                ->whereBetween('transactions.created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]) // Check within current year
                ->groupBy('month', 'month_number')
                ->orderBy('month_number', 'asc')
                ->get();



            // Revenue By Transaction Type
            $revenueByType = TransactionItem::select(
                DB::raw('SUM(transaction_items.price * transaction_items.quantity) as revenue'),
                'transactions.type'
            )
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->whereIn('transactions.status', $statusToRevenue)
                ->groupBy('transactions.type')
                ->get();


            $currentYear = Carbon::now()->year;
            $sellingTransactionsByType = [];
            for ($trend = 0; $trend < count($transactionTrends); $trend++) {
                $column = ($transactionTrends[$trend] == 'Incomming') ? 'transactions.receiver_business_id' : 'transactions.initiator_id';

                for ($month = 1; $month <= 12; $month++) {
                    $carbonDate = Carbon::create($currentYear, $month, 1);
                    $monthName = $carbonDate->format('F');
                    $formattedMonth = $carbonDate->format('Y-m');

                    $sellingTransactionsByType[$transactionTrends[$trend]][$monthName] = TransactionItem::select(
                        DB::raw('COUNT(*) as count'),
                        'transactions.type',
                        DB::raw('DATE_FORMAT(transactions.created_at, "%Y-%m") as month')
                    )
                        ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                        ->where($column, $business_id)
                        ->where(DB::raw('DATE_FORMAT(transactions.created_at, "%Y-%m")'), $formattedMonth)
                        ->groupBy('month', 'transactions.type')
                        ->get();
                }
            }




            $transactionsPerDay = [];

            for ($trend = 0; $trend < count($transactionTrends); $trend++) {
                $startOfTheWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
                $endOfTheWeek = Carbon::now()->endOfWeek(Carbon::SUNDAY);

                for ($date = $startOfTheWeek->copy(); $date->lte($endOfTheWeek); $date->addDay()) {
                    if ($transactionTrends[$trend] == 'Incomming') {
                        $transactionCount = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                            ->where('transactions.receiver_business_id', $business_id)
                            ->whereDate('transactions.created_at', $date)
                            ->count();

                        $transactionsPerDay['Incomming'][$date->format('l')] = $transactionCount;
                    } else if ($transactionTrends[$trend] == 'Outgoing') {
                        $transactionCount = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                            ->where('transactions.initiator_id', $business_id)
                            ->whereDate('transactions.created_at', $date)
                            ->count();

                        $transactionsPerDay['Outgoing'][$date->format('l')] = $transactionCount;
                    }
                }
            }


            $revenueByTypeAndMonth = TransactionItem::select(
                DB::raw('DATE_FORMAT(transactions.created_at, "%M") as month'),
                DB::raw('SUM(transaction_items.price * transaction_items.quantity) as revenue'),
                'transactions.type',
            )
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->where('transactions.initiator_id', $business_id)
                ->whereIn('transactions.status', $statusToRevenue)
                ->whereYear('transactions.created_at', now()->year)
                ->groupBy('month', 'transactions.type')
                ->get();





            // Prepare Dashboard Data
            $dashboardData = [
                'revenueSummary' => [
                    $todayRevenueSummary,
                    $todayMonthRevenueSummary,
                    $todayYearRevenueSummary,
                ],
                'businessSummary' => $businessSummary,
                'weeklyTransactionTrends' => $transactionsPerDay,
                'revenueByTransaction' => $revenueByTypeAndMonth,
                'lowStockItems' => $lowStockItems,
                'revenueTrends' => $revenueTrends,
                'revenueByType' => $revenueByType,
                'transactionTypeCount' => $sellingTransactionsByType,
            ];

            // Return the response
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
