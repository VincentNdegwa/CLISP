<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustmentReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockAdjustmentReasonController extends Controller
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

        $query = StockAdjustmentReason::query();

        $query->forBusiness($request->business_id);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $query->orderBy('name');
        
        if ($request->has('rows')) {
            $reasons = $query->paginate($request->rows);
        } else {
            $reasons = $query->get();
        }

        return response()->json($reasons);
    }

  
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_id' => 'required|exists:business,business_id',
            'name' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'adjustment_type' => 'required|string|in:increase,decrease',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $reason = StockAdjustmentReason::create($request->all());

        return response()->json([
            'message' => 'Stock adjustment reason created successfully',
            'reason' => $reason
        ], 201);
    }

 
    public function show($id)
    {
        $reason = StockAdjustmentReason::findOrFail($id);
        
        return response()->json($reason);
    }

    public function update(Request $request, $id)
    {
        $reason = StockAdjustmentReason::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'adjustment_type' => 'sometimes|required|string|in:increase,decrease',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $reason->update($request->all());

        return response()->json([
            'message' => 'Stock adjustment reason updated successfully',
            'reason' => $reason
        ]);
    }

 
    public function destroy($id)
    {
        $reason = StockAdjustmentReason::findOrFail($id);
        
        if ($reason->stockMovements()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete reason that is used in stock movements'
            ], 422);
        }

        $reason->delete();

        return response()->json([
            'message' => 'Stock adjustment reason deleted successfully'
        ]);
    }
}
