<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
            $validatedData = $request->validate([
                'business_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:business,email',
                'registration_number' => 'required|string|max:50|unique:business,registration_number',
                'date_registered' => 'required|date',
                'business_type_id' => 'required|exists:business_types,id',
                'industry_id' => 'required|exists:industries,id',
                'location' => 'required',
                'user_id' => 'required|exists:users,id'
            ]);

            // $business = Business::create($validatedData);
            // $user = User::where('id', $validatedData['user_id'])->first();

            // $user->update([
            //     "busines_id" => $business->id
            // ]);
            // $newBusiness = Business::with(['businessType', 'industry'])->where("business_id", $business->id)->first();
            return response()->json([
                'error' => false,
                'message' => 'Business created successfully!',
                // 'data' => $newBusiness
            ], 201); // HTTP 201 Created
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
            ], 500); // HTTP 500 Internal Server Error
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
}
