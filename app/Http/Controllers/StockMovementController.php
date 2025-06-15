<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockMovementController extends Controller
{
    /**
     * Display a listing of stock movements.
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
            'from_date' => 'sometimes|date',
            'to_date' => 'sometimes|date|after_or_equal:from_date',
            'warehouse_id' => 'sometimes|exists:warehouses,id',
            'item_id' => 'sometimes|exists:items,id',
            'movement_type' => 'sometimes|string|in:in,out,transfer,adjustment',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $query = StockMovement::query();

        // Filter by business_id
        $query->whereHas('inventory', function ($q) use ($request) {
            $q->where('business_id', $request->business_id);
        });

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('sourceInventory', function ($sq) use ($request) {
                    $sq->where('warehouse_id', $request->warehouse_id);
                })->orWhereHas('destinationInventory', function ($sq) use ($request) {
                    $sq->where('warehouse_id', $request->warehouse_id);
                });
            });
        }

        // Filter by item
        if ($request->has('item_id')) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('item_id', $request->item_id);
            });
        }

        // Filter by movement type
        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('reason', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('inventory.item', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Include relationships
        $query->with([
            'inventory.item', 
            'inventory.warehouse', 
            'inventory.binLocation',
            'sourceInventory.warehouse',
            'sourceInventory.binLocation',
            'destinationInventory.warehouse',
            'destinationInventory.binLocation',
            'reason'
        ]);

        // Order by created_at desc
        $query->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->rows ?? 20;
        $stockMovements = $query->paginate($perPage);

        return response()->json($stockMovements);
    }

    /**
     * Get stock movements by inventory ID.
     *
     * @param  int  $inventoryId
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getByInventory($inventoryId, Request $request)
    {
        $validator = Validator::make(['inventory_id' => $inventoryId] + $request->all(), [
            'inventory_id' => 'required|exists:inventory,id',
            'page' => 'sometimes|integer|min:1',
            'rows' => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $query = StockMovement::where(function ($q) use ($inventoryId) {
            $q->where('inventory_id', $inventoryId)
              ->orWhere('source_inventory_id', $inventoryId)
              ->orWhere('destination_inventory_id', $inventoryId);
        });
        
        // Include relationships
        $query->with([
            'inventory.item', 
            'inventory.warehouse', 
            'inventory.binLocation',
            'sourceInventory.warehouse',
            'sourceInventory.binLocation',
            'destinationInventory.warehouse',
            'destinationInventory.binLocation',
            'reason'
        ]);

        // Order by created_at desc
        $query->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->rows ?? 20;
        $stockMovements = $query->paginate($perPage);

        return response()->json($stockMovements);
    }

    /**
     * Store a newly created stock movement in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inventory_id' => 'required|exists:inventory,id',
            'quantity' => 'required|numeric|min:0.01',
            'movement_type' => 'required|string|in:in,out,transfer,adjustment',
            'reason_id' => 'required|exists:stock_movement_reasons,id',
            'reference' => 'sometimes|nullable|string|max:255',
            'source_inventory_id' => 'sometimes|nullable|exists:inventory,id',
            'destination_inventory_id' => 'sometimes|nullable|exists:inventory,id',
            'notes' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the stock movement record
            $stockMovement = StockMovement::create($request->all());

            // Update inventory quantities based on movement type
            $inventory = Inventory::findOrFail($request->inventory_id);
            
            switch ($request->movement_type) {
                case 'in':
                    $inventory->quantity += $request->quantity;
                    break;
                case 'out':
                    if ($inventory->quantity < $request->quantity) {
                        throw new \Exception('Insufficient inventory quantity');
                    }
                    $inventory->quantity -= $request->quantity;
                    break;
                case 'transfer':
                    if ($inventory->quantity < $request->quantity) {
                        throw new \Exception('Insufficient inventory quantity for transfer');
                    }
                    
                    $inventory->quantity -= $request->quantity;
                    
                    // If destination inventory exists, add quantity
                    if ($request->has('destination_inventory_id') && $request->destination_inventory_id) {
                        $destinationInventory = Inventory::findOrFail($request->destination_inventory_id);
                        $destinationInventory->quantity += $request->quantity;
                        $destinationInventory->save();
                    }
                    break;
                case 'adjustment':
                    // Adjustment is handled directly by the InventoryController::adjustQuantity method
                    // This is just for recording the movement
                    break;
            }
            
            $inventory->save();
            
            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Stock movement created successfully',
                'stock_movement' => $stockMovement->load([
                    'inventory.item', 
                    'inventory.warehouse', 
                    'inventory.binLocation',
                    'sourceInventory.warehouse',
                    'sourceInventory.binLocation',
                    'destinationInventory.warehouse',
                    'destinationInventory.binLocation',
                    'reason'
                ])
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            return response()->json([
                'message' => 'Error creating stock movement: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified stock movement.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $stockMovement = StockMovement::with([
            'inventory.item', 
            'inventory.warehouse', 
            'inventory.binLocation',
            'sourceInventory.warehouse',
            'sourceInventory.binLocation',
            'destinationInventory.warehouse',
            'destinationInventory.binLocation',
            'reason'
        ])->findOrFail($id);
        
        return response()->json($stockMovement);
    }

    /**
     * Update the specified stock movement in storage.
     * Note: This should generally be avoided as it can lead to inventory discrepancies.
     * Only non-quantity fields should be updated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $stockMovement = StockMovement::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'reason_id' => 'sometimes|required|exists:stock_movement_reasons,id',
            'reference' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Only allow updating non-quantity fields
        $stockMovement->update($request->only(['reason_id', 'reference', 'notes']));

        return response()->json([
            'message' => 'Stock movement updated successfully',
            'stock_movement' => $stockMovement->load([
                'inventory.item', 
                'inventory.warehouse', 
                'inventory.binLocation',
                'sourceInventory.warehouse',
                'sourceInventory.binLocation',
                'destinationInventory.warehouse',
                'destinationInventory.binLocation',
                'reason'
            ])
        ]);
    }

    /**
     * Remove the specified stock movement from storage.
     * Note: This should generally be avoided as it can lead to inventory discrepancies.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return response()->json([
            'message' => 'Stock movements cannot be deleted to maintain inventory integrity'
        ], 422);
    }
}
