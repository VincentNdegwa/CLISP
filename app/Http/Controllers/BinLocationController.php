<?php

namespace App\Http\Controllers;

use App\Models\BinLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BinLocationController extends Controller
{
   
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'sometimes|exists:warehouses,id',
            'page' => 'sometimes|integer|min:1',
            'rows' => 'sometimes|integer|min:1|max:100',
            'search' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $query = BinLocation::query();

        // Filter by warehouse_id if provided
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Include warehouse relationship
        $query->with('warehouse');

        // Pagination
        $perPage = $request->rows ?? 20;
        $binLocations = $query->paginate($perPage);

        return response()->json($binLocations);
    }

    /**
     * Get bin locations by warehouse ID.
     *
     * @param  int  $warehouseId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getByWarehouse($warehouseId, Request $request)
    {
        $validator = Validator::make(['warehouse_id' => $warehouseId] + $request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'page' => 'sometimes|integer|min:1',
            'rows' => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $query = BinLocation::where('warehouse_id', $warehouseId);
        
        // Include warehouse relationship
        $query->with('warehouse');

        // Pagination
        $perPage = $request->rows ?? 20;
        $binLocations = $query->paginate($perPage);

        return response()->json($binLocations);
    }

    /**
     * Store a newly created bin location in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'required|exists:warehouses,id',
            'name' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'aisle' => 'sometimes|nullable|string|max:50',
            'rack' => 'sometimes|nullable|string|max:50',
            'shelf' => 'sometimes|nullable|string|max:50',
            'bin' => 'sometimes|nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $binLocation = BinLocation::create($request->all());

        return response()->json([
            'message' => 'Bin location created successfully',
            'bin_location' => $binLocation
        ], 201);
    }

    /**
     * Display the specified bin location.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $binLocation = BinLocation::with('warehouse')->findOrFail($id);
        
        return response()->json($binLocation);
    }

    /**
     * Update the specified bin location in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $binLocation = BinLocation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'aisle' => 'sometimes|nullable|string|max:50',
            'rack' => 'sometimes|nullable|string|max:50',
            'shelf' => 'sometimes|nullable|string|max:50',
            'bin' => 'sometimes|nullable|string|max:50',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $binLocation->update($request->all());

        return response()->json([
            'message' => 'Bin location updated successfully',
            'bin_location' => $binLocation
        ]);
    }

    /**
     * Remove the specified bin location from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $binLocation = BinLocation::findOrFail($id);
        
        // Check if bin location has any inventory before deleting
        if ($binLocation->inventories()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete bin location with existing inventory items'
            ], 422);
        }

        $binLocation->delete();

        return response()->json([
            'message' => 'Bin location deleted successfully'
        ]);
    }
}
