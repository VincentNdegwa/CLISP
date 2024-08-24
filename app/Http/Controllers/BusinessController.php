<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|string',
            'registration_number' => 'required|string|unique:' . Business::class,
            'date_registered' => 'required',
        ]);

        $business = Business::create($request->all());

        return response()->json([
            "error" => false,
            "message" => 'Business Created!',
            "data" => $business
        ]);
    }


    public function Update(Request $request)
    {
        $request->validate([
            'busines_id' => 'required|exists:business,business_id',
            'business_name' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|string',
        ]);

        $business = Business::where("business_id", $request->input("business_id"))->first();
        $business->update($request->all());
        return response()->json([
            "error" => false,
            "message" => "Business Updated!",
            "date" => $business,
        ]);
    }


    public function Delete(Request $request)
    {
        $request->validate([
            'business_id' => 'required|exists:business,business_id',
        ]);
        $business = Business::where('business_id', $request->input('business_id'))->first();
        $business->delete();
        return response()->json([
            'error' => false,
            'message' => 'Business Deleted'
        ]);
    }
}
