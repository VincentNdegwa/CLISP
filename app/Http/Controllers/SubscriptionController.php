<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function create(Request $request)
    {
        $businessId = $request->query('id');

        $business = Business::where("business_id", $businessId)->first();
        if (!$business->subscription_plan) {
            Inertia::render("Auth/ChoosePlan", [
                "business" => $business
            ]);
        };
    }
    public function pay(Request $request)
    {


        try {
            $validate = $request->validate([
                'business' => 'required|exists:business,business_id',
                "cardName" => 'required|string',
                "subscription_name" => 'required|string',
                "subscription_amount" => 'required|string',
                "cardNumber" => 'required|string',
                "expiryDate" => 'required|string',
                "cvc" => 'required|string',
                "billingAddress" => 'required|string',
            ]);

            $subscription = Subscription::create([
                "name" => $validate['subscription_name'],
                // "subscription_start" => $validate[''],
                // "subscription_end" => $validate[''],
                // "payment_status" => $validate[''],
                "amount_paid" => $validate['subscription_amount'],
                "payment_status" => 'Paid'
            ]);

            $subscription_id = $subscription->id;
            Business::where('business_id', $validate['business'])->update([
                'subscription_plan' => $subscription_id,
            ]);

            return response()->json([
                'error' => false,
                'message' => 'Business Billed Successfully!!',
                'data' => Business::where("business_id", $validate['business'])->first()
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
