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

    public function getBillingTransactions($business_id)
    {
        $transactions = SubscriptionTransaction::where('billable_type', 'App\Models\Business')
            ->where('billable_id', $business_id)
            ->paginate(20);

        return response()->json($transactions);
    }
}
