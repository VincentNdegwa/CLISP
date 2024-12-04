<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Stripe\Account;


class BusinessController extends Controller
{
    /**
     * Create a new business entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */



    public function create(Request $request)
    {

        try {
            DB::beginTransaction();
            $validatedData = $request->validate([
                'business_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'registration_number' => 'required|string|max:50|unique:business,registration_number',
                'date_registered' => 'required|date',
                'business_type_id' => 'required|exists:business_types,id',
                'industry_id' => 'required|exists:industries,id',
                'location' => 'required',
                'user_id' => 'required|exists:users,id',
                'logo' => 'nullable|string',
                'currency_code' => 'required|string'
            ]);




            $business = Business::create($validatedData);

            BusinessUser::create([
                "business_id" => $business->business_id,
                "user_id" => $validatedData['user_id'],
                'role' => 'Owner'
            ]);

            $newBusiness = Business::with(['businessType', 'industry'])->where("business_id", $business->business_id)->first();
            DB::commit();
            return response()->json([
                'error' => false,
                'message' => 'Business created successfully!',
                'data' => $newBusiness
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update an existing business entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'business_id' => 'required|exists:business,business_id',
                'business_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'date_registered' => 'required|date',
                "logo" => "nullable|string"
            ]);

            $business = Business::where('business_id', $validatedData['business_id'])->firstOrFail();
            $business->update($validatedData);


            return response()->json([
                'error' => false,
                'message' => 'Business updated successfully!',
                'data' => $business
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422); // HTTP 422 Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    /**
     * Delete an existing business entry.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'business_id' => 'required|exists:business,business_id',
            ]);

            $business = Business::where('business_id', $validatedData['business_id'])->firstOrFail();
            $business->delete();

            return response()->json([
                'error' => false,
                'message' => 'Business deleted successfully!'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    public function getDetails()
    {
        $businessTypes = DB::table("business_types")->get(["id", "name"]);
        $industries = DB::table("industries")->get(['id', 'name']);

        return response()->json([
            'businessTypes' => $businessTypes,
            'industries' => $industries,
        ]);
    }
    public function fetchMyBusiness(Request $request)
    {

        try {
            $validate = $request->validate([
                "userId" => 'required|exists:users,id',
                'businessId' => 'required|exists:business,business_id'
            ]);
            $user_business = BusinessUser::where("user_id", $validate['userId'])
                ->where('business_id', $validate['businessId'])
                ->with([

                    'business' => function ($query) use ($validate) {
                        $query->with(['businessType', 'industry']);
                    },
                ])->first();
            return response()->json([
                'error' => false,
                'message' => 'Business fetched',
                'data' => $user_business
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Validation error.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ], 500); // HTTP 500 Internal Server Error
        }
    }

    public function getBusinessSearch(Request $request)
    {
        $search = $request->query('term');
        $businesses = Business::where("status", 'active')
            ->where('business_name', 'LIKE', "%{$search}%")
            ->orWhere("registration_number", 'LIKE', "%{$search}%")
            ->orWhere("email", 'LIKE', "%{$search}%")
            ->get();
        return response()->json([
            'error' => false,
            'businesses' => $businesses,
            'business_count' => count($businesses),
        ]);
    }

    public function getPaymentMethods(Request $request, $business_id)
    {
        $methods  =  PaymentMethod::where(function ($query) use ($business_id) {
            $query->where("business_id", $business_id)->orWhere("default", 'true');
        })->get();

        return response()->json($methods);
    }
}
