<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarrierController extends Controller
{
    /**
     * Display a listing of carriers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:business,business_id',
            'page' => 'sometimes|integer|min:1',
            'rows' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $query = Carrier::query();

        // Filter by business_id
        $query->where('business_id', $request->business_id);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tracking_url', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->rows ?? 20;
        $carriers = $query->paginate($perPage);

        return response()->json($carriers);
    }

    /**
     * Store a newly created carrier in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:business,business_id',
            'name' => 'required|string|max:255',
            'tracking_url' => 'sometimes|nullable|string|max:255',
            'account_number' => 'sometimes|nullable|string|max:100',
            'contact_name' => 'sometimes|nullable|string|max:255',
            'contact_phone' => 'sometimes|nullable|string|max:20',
            'contact_email' => 'sometimes|nullable|email|max:255',
            'is_active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $carrier = Carrier::create($request->all());

        return response()->json([
            'message' => 'Carrier created successfully',
            'carrier' => $carrier
        ], 201);
    }

    /**
     * Display the specified carrier.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $carrier = Carrier::findOrFail($id);
        
        return response()->json($carrier);
    }

    /**
     * Update the specified carrier in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $carrier = Carrier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'tracking_url' => 'sometimes|nullable|string|max:255',
            'account_number' => 'sometimes|nullable|string|max:100',
            'contact_name' => 'sometimes|nullable|string|max:255',
            'contact_phone' => 'sometimes|nullable|string|max:20',
            'contact_email' => 'sometimes|nullable|email|max:255',
            'is_active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $carrier->update($request->all());

        return response()->json([
            'message' => 'Carrier updated successfully',
            'carrier' => $carrier
        ]);
    }

    /**
     * Remove the specified carrier from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $carrier = Carrier::findOrFail($id);
        
        // Check if carrier is used in any shipments before deleting
        if ($carrier->shipments()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete carrier with existing shipments'
            ], 422);
        }

        $carrier->delete();

        return response()->json([
            'message' => 'Carrier deleted successfully'
        ]);
    }
}
