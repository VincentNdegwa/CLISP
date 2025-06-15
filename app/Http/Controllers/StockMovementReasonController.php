<?php

namespace App\Http\Controllers;

use App\Models\StockMovementReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockMovementReasonController extends Controller
{
    /**
     * Display a listing of stock movement reasons.
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
            'movement_type' => 'sometimes|string|in:in,out,transfer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $query = StockMovementReason::query();

        // Filter by business_id
        $query->where('business_id', $request->business_id);

        // Filter by movement type if provided
        if ($request->has('movement_type') && !empty($request->movement_type)) {
            $query->where('movement_type', $request->movement_type);
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Order by name
        $query->orderBy('name');

        // Pagination
        $perPage = $request->rows ?? 20;
        $reasons = $query->paginate($perPage);

        return response()->json($reasons);
    }

    /**
     * Store a newly created stock movement reason in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:business,business_id',
            'name' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'movement_type' => 'required|string|in:in,out,transfer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $reason = StockMovementReason::create($request->all());

        return response()->json([
            'message' => 'Stock movement reason created successfully',
            'reason' => $reason
        ], 201);
    }

    /**
     * Display the specified stock movement reason.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reason = StockMovementReason::findOrFail($id);
        
        return response()->json($reason);
    }

    /**
     * Update the specified stock movement reason in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $reason = StockMovementReason::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'movement_type' => 'sometimes|required|string|in:in,out,transfer',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $reason->update($request->all());

        return response()->json([
            'message' => 'Stock movement reason updated successfully',
            'reason' => $reason
        ]);
    }

    /**
     * Remove the specified stock movement reason from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $reason = StockMovementReason::findOrFail($id);
        
        // Check if reason is used in any stock movements before deleting
        if ($reason->stockMovements()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete reason that is used in stock movements'
            ], 422);
        }

        $reason->delete();

        return response()->json([
            'message' => 'Stock movement reason deleted successfully'
        ]);
    }
}
