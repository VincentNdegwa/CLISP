<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
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
}
