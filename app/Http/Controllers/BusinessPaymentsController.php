<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class BusinessPaymentsController extends Controller
{
    public function getPaymentMethods(Request $request, $business_id)
    {
        $methods = PaymentMethod::where(function ($query) use ($business_id) {
            $query->where("business_id", $business_id)
                ->orWhere("default", 'true');
        });

        if ($request->has('category')) {
            $methods->where('category', $request->category);
        }

        if ($request->has('search')) {
            $methods->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status')) {
            $methods->where('status', $request->status);
        }


        $methods = $methods->get();

        return response()->json($methods);
    }
}
