<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{

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

        $query = Warehouse::query();

        $query->where('business_id', $request->business_id);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $perPage = $request->rows ?? 20;
        $warehouses = $query->paginate($perPage);

        return response()->json($warehouses);
    }

    public function store(Request $request)
    {
        if (!$request->filled('code')) {
            $request->merge([
                'code' => $this->generateUniqueWarehouseCode($request->business_id)
            ]);
        }

        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:business,business_id',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
            'email' => 'sometimes|nullable|email|max:255',
            'code' => 'required|string|max:50|unique:warehouses,code',
            'is_active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $warehouse = Warehouse::create($request->all());

        return response()->json([
            'message' => 'Warehouse created successfully',
            'warehouse' => $warehouse
        ], 201);
    }

    private function generateUniqueWarehouseCode($businessId)
    {
        $prefix = 'WH';
        $businessPrefix = substr($businessId, 0, 3);
        
        $timestamp = now()->format('ymd');
        
        $count = Warehouse::where('business_id', $businessId)->count() + 1;
        
        $formattedCount = str_pad($count, 3, '0', STR_PAD_LEFT);
        
        $code = $prefix . '-' . $businessPrefix . '-' . $timestamp . '-' . $formattedCount;
        
        while (Warehouse::where('code', $code)->exists()) {
            $count++;
            $formattedCount = str_pad($count, 3, '0', STR_PAD_LEFT);
            $code = $prefix . '-' . $businessPrefix . '-' . $timestamp . '-' . $formattedCount;
        }
        
        return $code;
    }
 
    public function show($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        return response()->json($warehouse);
    }


    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'postal_code' => 'sometimes|required|string|max:20',
            'country' => 'sometimes|required|string|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
            'email' => 'sometimes|nullable|email|max:255',
            'is_active' => 'sometimes|boolean',
            'notes' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $warehouse->update($request->all());

        return response()->json([
            'message' => 'Warehouse updated successfully',
            'warehouse' => $warehouse
        ]);
    }

  
    public function destroy($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        if ($warehouse->inventories()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete warehouse with existing inventory items'
            ], 422);
        }

        if ($warehouse->binLocations()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete warehouse with existing bin locations'
            ], 422);
        }

        $warehouse->delete();

        return response()->json([
            'message' => 'Warehouse deleted successfully'
        ]);
    }
    
}
