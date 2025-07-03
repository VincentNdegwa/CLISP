<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InventoryAdjustmentController extends Controller
{
    /**
     * Display a listing of inventory adjustments.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Inventory/Adjustments/Index');
    }

    /**
     * Show the form for creating a new inventory adjustment.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Inventory/Adjustments/Create');
    }

    /**
     * Display the specified inventory adjustment.
     *
     * @param  \App\Models\InventoryAdjustment  $adjustment
     * @return \Inertia\Response
     */
    public function show(InventoryAdjustment $adjustment)
    {
        // Load relationships needed for the view
        $adjustment->load(['items.inventory', 'reason', 'warehouse', 'createdBy', 'approvedBy']);
        
        return Inertia::render('Inventory/Adjustments/Show', [
            'adjustment' => $adjustment
        ]);
    }

    /**
     * API Methods
     */

    /**
     * Display a listing of the inventory adjustments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiIndex(Request $request)
    {
        $query = InventoryAdjustment::query()
            ->with(['warehouse', 'reason', 'createdBy']);
            
        // Add filters as needed
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        
        // Sorting
        $query->orderBy('created_at', 'desc');
        
        $adjustments = $query->paginate(15);
        
        return response()->json($adjustments);
    }

    /**
     * Store a newly created inventory adjustment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiStore(Request $request)
    {
        // Validate the request
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason_id' => 'required|exists:stock_adjustment_reasons,id',
            'notes' => 'nullable|string',
            'items' => 'required|array',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.quantity' => 'required|numeric',
            'items.*.bin_location_id' => 'nullable|exists:bin_locations,id',
        ]);

        // Create the inventory adjustment
        $adjustment = InventoryAdjustment::create([
            'warehouse_id' => $request->warehouse_id,
            'reason_id' => $request->reason_id,
            'notes' => $request->notes,
            'status' => 'pending', // Default status
            'created_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        // Create adjustment items
        foreach ($request->items as $item) {
            InventoryAdjustmentItem::create([
                'inventory_adjustment_id' => $adjustment->id,
                'inventory_id' => $item['inventory_id'],
                'quantity' => $item['quantity'],
                'bin_location_id' => $item['bin_location_id'] ?? null,
            ]);
        }

        // Load relationships for the response
        $adjustment->load(['items.inventory', 'warehouse', 'reason']);

        return response()->json([
            'message' => 'Inventory adjustment created successfully',
            'adjustment' => $adjustment
        ], 201);
    }

    /**
     * Display the specified inventory adjustment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiShow($id)
    {
        $adjustment = InventoryAdjustment::with(['items.inventory', 'warehouse', 'reason', 'createdBy', 'approvedBy'])
            ->findOrFail($id);
            
        return response()->json($adjustment);
    }

    /**
     * Update the specified inventory adjustment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUpdate(Request $request, $id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);
        
        // Can only update if pending
        if ($adjustment->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot update an adjustment that has been approved or processed'
            ], 422);
        }

        // Validate the request
        $request->validate([
            'warehouse_id' => 'sometimes|exists:warehouses,id',
            'reason_id' => 'sometimes|exists:stock_adjustment_reasons,id',
            'notes' => 'nullable|string',
            'items' => 'sometimes|array',
            'items.*.id' => 'sometimes|exists:inventory_adjustment_items,id',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.quantity' => 'required|numeric',
            'items.*.bin_location_id' => 'nullable|exists:bin_locations,id',
        ]);

        // Update adjustment
        $adjustment->update([
            'warehouse_id' => $request->warehouse_id ?? $adjustment->warehouse_id,
            'reason_id' => $request->reason_id ?? $adjustment->reason_id,
            'notes' => $request->notes ?? $adjustment->notes,
        ]);

        // Update items if provided
        if ($request->has('items')) {
            // Remove existing items
            $adjustment->items()->delete();
            
            // Create new items
            foreach ($request->items as $item) {
                InventoryAdjustmentItem::create([
                    'inventory_adjustment_id' => $adjustment->id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'bin_location_id' => $item['bin_location_id'] ?? null,
                ]);
            }
        }

        // Reload the adjustment with relationships
        $adjustment->load(['items.inventory', 'warehouse', 'reason']);

        return response()->json([
            'message' => 'Inventory adjustment updated successfully',
            'adjustment' => $adjustment
        ]);
    }

    /**
     * Remove the specified inventory adjustment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiDestroy($id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);
        
        // Can only delete if pending
        if ($adjustment->status !== 'pending') {
            return response()->json([
                'message' => 'Cannot delete an adjustment that has been approved or processed'
            ], 422);
        }

        // Delete items first to avoid foreign key constraint errors
        $adjustment->items()->delete();
        $adjustment->delete();

        return response()->json([
            'message' => 'Inventory adjustment deleted successfully'
        ]);
    }

    /**
     * Approve an inventory adjustment and process the quantity changes.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiApprove($id)
    {
        $adjustment = InventoryAdjustment::with('items.inventory')->findOrFail($id);
        
        // Can only approve if pending
        if ($adjustment->status !== 'pending') {
            return response()->json([
                'message' => 'This adjustment has already been processed'
            ], 422);
        }

        // Process each item in the adjustment
        foreach ($adjustment->items as $item) {
            $inventory = $item->inventory;
            
            // Update inventory quantity
            $newQuantity = $inventory->quantity + $item->quantity;
            
            // Ensure quantity doesn't go negative
            if ($newQuantity < 0) {
                return response()->json([
                    'message' => "Cannot adjust inventory {$inventory->id} to negative quantity"
                ], 422);
            }
            
            $inventory->quantity = $newQuantity;
            $inventory->save();

            // Record the movement in stock_movements table if needed
            // This would be handled by a separate service or model method
        }

        // Update the adjustment status
        $adjustment->update([
            'status' => 'approved',
            'approved_by' => \Illuminate\Support\Facades\Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Inventory adjustment approved successfully',
            'adjustment' => $adjustment
        ]);
    }
}
