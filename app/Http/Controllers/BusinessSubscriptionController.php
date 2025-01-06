<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\SubscriptionPlan;
use App\Models\Paddle\SubscriptionTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusinessSubscriptionController extends Controller
{
    public function index()
    {
        return Inertia::render("Billing/Billing");
    }

    public function getBilling($business_id)
    {
        $business = Business::with(['subscriptions.transactions'])->where('business_id', $business_id)->first();

        if (!$business) {
            return response()->json(['error' => 'Business not found'], 404);
        }

        $subscriptions = $business->subscriptions->map(function ($subscription) {
            $items = $subscription->items->map(function ($item) {
                $item->plan = SubscriptionPlan::where('price_id', $item->price_id)->first();
                return [
                    'id' => $item->id,
                    'price_id' => $item->price_id,
                    'plan' => [
                        'id' => $item->plan->id ?? null,
                        'name' => $item->plan->name ?? null,
                        'description' => $item->plan->description ?? null,
                        'price' => $item->plan->price ?? null,
                        'currency' => $item->plan->currency ?? null,
                        'billing_cycle' => $item->plan->billing_cycle ?? null,
                        'price_id' => $item->plan->price_id ?? null,
                    ],
                ];
            });

            return [
                'id' => $subscription->id,
                'name' => $subscription->items->first()?->plan?->name ?? 'Unknown Plan',
                'ends_at' => $subscription->ends_at,
                'status' => $subscription->status,
                'type' => $subscription->type,
                'items' => $items,
            ];
        });

        $cleanedBusiness = [
            'business_id' => $business->business_id,
            'business_name' => $business->business_name,
            'last_payment' => $business->subscription('default')->lastPayment(),
            'next_payment' => $business->subscription('default')->nextPayment(),
            'subscriptions' => $subscriptions,
        ];

        return response()->json($cleanedBusiness);
    }

    public function getBillingTransactions(Request $request, $business_id)
    {
        $rows = $request->input('rows', 20);
        $page = $request->input('page', 1);

        $transactions = SubscriptionTransaction::where('billable_type', 'App\Models\Business')
            ->where('billable_id', $business_id)
            ->paginate($rows, ['*'], 'page', $page);

        $formattedTransactions = [
            'current_page' => $transactions->currentPage(),
            'data' => $transactions->getCollection()->transform(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'status' => $transaction->status,
                    'total' => $transaction->total(),
                    'tax' => $transaction->tax(),
                    'currency' => $transaction->currency,
                    'billed_at' => $transaction->billed_at,
                ];
            }),
            'total' => $transactions->total(),
            'per_page' => $transactions->perPage(),
        ];

        return response()->json($formattedTransactions);
    }


    public function cancelSubscription($business_id)
    {
        $business = Business::where('business_id', $business_id)->first();

        if (!$business) {
            return response()->json(['message' => 'Business not found', 'error' => true]);
        }

        $business->subscription('default')->cancel();

        return response()->json(['message' => 'Subscription cancelled successfully', "error" => false]);
    }
}
