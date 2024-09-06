<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function create(Request $request)
    {

        try {
            $request->validate([
                'full_names' => 'required',
                'email' => 'required|email',
                'phone_number' => 'required',
                'address' => 'required',
                'business_id' => 'required|exists:business,business_id'
            ]);

            $customer = Customer::create($request->all());
            $created_customer = Customer::with('business')->find($customer->id);
            return response()->json([
                'error' => false,
                'message' => 'Customer created successfully',
                'data' => $created_customer
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

    public function getBusinessCustomers($business_id)
    {
        $business = Customer::where('business_id', $business_id)->with('business')->get();
        if (!$business) {
            return response()->json([
                'error' => true,
                'message' => 'Business not found.',
            ]);
        }
        return response()->json([
            'error' => false,
            'message' => 'Customers retrieved successfully.',
            'data' => $business
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'full_names' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required',
            'address' => 'required',
            'business_id' => 'required|exists:business,business_id',
            'id' => 'required|exists:customers,id'
        ]);
        $customer = Customer::find($request->input('id'));
        if (!$customer) {
            return response()->json([
                'error' => true,
                'message' => 'Customer not found.',
            ]);
        }
        $customer->update($request->all());
        return response()->json([
            "error" => true,
            "message" => "Customer updated successfully",
            "data" => $customer,
        ]);
    }

    public function delete($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json([
                'error' => true,
                'message' => 'Customer not found.',
            ]);
        }
        $customer->delete();
        return response()->json([
            'error' => false,
            'message' => 'Customer deleted successfully.',
        ]);
    }
}
