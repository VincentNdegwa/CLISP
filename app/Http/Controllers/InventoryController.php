<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\BinLocation;
use Illuminate\Http\Request;
use App\Models\StockMovement;
use App\Models\WarehouseZone;
use App\Models\InventoryBatch;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function resources()
    {
        return Inertia::render('Inventory/Resources/Index');
    }
    
    public function inventory(){
        return Inertia::render('Inventory/Inventory/Index');
    }

    public function index(Request $request)
    {
        $query = Inventory::with(['item', 'warehouse', 'binLocation'])
            ->where('business_id', $request->business_id);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('bin_location_id')) {
            $query->where('bin_location_id', $request->bin_location_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $inventories = $query->paginate($request->input('rows', 20));

        return response()->json($inventories);
    }

    public function show($id)
    {
        $inventory = Inventory::with(['item', 'warehouse', 'binLocation', 'batches'])
            ->findOrFail($id);

        return response()->json($inventory);
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:resource_item,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'bin_location_id' => 'required|exists:bin_locations,id',
            'quantity' => 'required|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            $warehouse = Warehouse::findOrFail($request->warehouse_id);
            $request->merge(['business_id' => $warehouse->business_id]);

            $inventory = Inventory::where('business_id', $warehouse->business_id)
                ->where('item_id', $request->item_id)
                ->where('warehouse_id', $warehouse->id)
                ->where('bin_location_id', $request->bin_location_id)
                ->first();

            if ($inventory) {
                // Update existing inventory
                $oldQuantity = $inventory->quantity;
                $inventory->quantity += $request->quantity;
                $inventory->reorder_point = $request->reorder_point ?? $inventory->reorder_point;
                $inventory->reorder_quantity = $request->reorder_quantity ?? $inventory->reorder_quantity;
                $inventory->save();
            } else {
                // Create new inventory
                $inventory = Inventory::create($request->all());
                $oldQuantity = 0;
            }

            // Create stock movement record
            StockMovement::create([
                'business_id' => $warehouse->business_id,
                'item_id' => $request->item_id,
                'warehouse_id' => $warehouse->id,
                'bin_location_id' => $request->bin_location_id,
                'batch_id' => $request->batch_id ?? null,
                'quantity' => $request->quantity,
                'movement_type' => 'adjustment',
                'reference_type' => 'inventory_adjustment',
                'reference_id' => $inventory->id,
                'notes' => $request->notes ?? 'Initial inventory setup',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Inventory created successfully',
                'inventory' => $inventory
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating inventory: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'sometimes|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
            'bin_location_id' => 'sometimes|exists:bin_locations,id',
        ]);

        try {
            DB::beginTransaction();

            $inventory = Inventory::findOrFail($id);
            $oldQuantity = $inventory->quantity;
            $quantityChange = 0;

            if ($request->has('quantity')) {
                $quantityChange = $request->quantity - $oldQuantity;
                $inventory->quantity = $request->quantity;
            }

            if ($request->has('reorder_point')) {
                $inventory->reorder_point = $request->reorder_point;
            }

            if ($request->has('reorder_quantity')) {
                $inventory->reorder_quantity = $request->reorder_quantity;
            }

            if ($request->has('bin_location_id')) {
                $inventory->bin_location_id = $request->bin_location_id;
            }

            $inventory->save();

            // Create stock movement record if quantity changed
            if ($quantityChange != 0) {
                StockMovement::create([
                    'business_id' => $inventory->business_id,
                    'item_id' => $inventory->item_id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'bin_location_id' => $inventory->bin_location_id,
                    'batch_id' => $request->batch_id ?? null,
                    'quantity' => $quantityChange,
                    'movement_type' => 'adjustment',
                    'reference_type' => 'inventory_adjustment',
                    'reference_id' => $inventory->id,
                    'notes' => $request->notes ?? 'Inventory adjustment',
                    'created_by' => Auth::id(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Inventory updated successfully',
                'inventory' => $inventory
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating inventory: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();

            return response()->json(['message' => 'Inventory deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting inventory: ' . $e->getMessage()], 500);
        }
    }

    public function getInventoryLevels(Request $request)
    {
        $query = Inventory::with(['item', 'warehouse', 'binLocation'])
            ->where('business_id', $request->business_id)
            ->select('item_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('item_id');

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $inventories = $query->paginate($request->input('rows', 20));

        return response()->json($inventories);
    }

    public function getLowStockItems(Request $request)
    {
        $query = Inventory::with(['item', 'warehouse', 'binLocation'])
            ->where('business_id', $request->business_id)
            ->whereRaw('quantity <= reorder_point')
            ->whereNotNull('reorder_point');

        $lowStockItems = $query->paginate($request->input('rows', 20));

        return response()->json($lowStockItems);
    }

    public function adjustQuantity($id, Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'adjustment_type' => 'required|in:increase,decrease',
            'reason_id' => 'required|exists:stock_adjustment_reasons,id',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        try {
            DB::beginTransaction();
            
            $inventory = Inventory::findOrFail($id);
            
            $currentQuantity = $inventory->quantity;
            $adjustmentAmount = $validated['quantity'];
            
            if ($validated['adjustment_type'] === 'increase') {
                $newQuantity = $currentQuantity + $adjustmentAmount;
            } else {
                $newQuantity = max(0, $currentQuantity - $adjustmentAmount);
                
                if ($adjustmentAmount > $currentQuantity) {
                    return response()->json([
                        'message' => 'Cannot subtract more than the current inventory quantity',
                        'errors' => ['quantity' => ['Not enough quantity available']]
                    ], 422);
                }
            }
            
            $inventory->quantity = $newQuantity;
            
            if ($newQuantity <= 0) {
                $inventory->status = Inventory::STATUS_OUT_OF_STOCK;
            } elseif ($newQuantity <= $inventory->reorder_point) {
                $inventory->status = Inventory::STATUS_LOW_STOCK;
            } else {
                $inventory->status = Inventory::STATUS_IN_STOCK;
            }
            
            $inventory->save();
            
            StockAdjustment::create([
                'inventory_id' => $inventory->id,
                'business_id' => $inventory->business_id,
                'user_id' => Auth::user()->id,
                'adjustment_type' => $validated['adjustment_type'],
                'quantity' => $validated['quantity'],
                'previous_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'reason_id' => $validated['reason_id'],
                'notes' => $validated['notes'] ?? null,
                'date' => now(),
            ]);
            
            StockMovement::create([
                'business_id' => $inventory->business_id,
                'item_id' => $inventory->item_id,
                'warehouse_id' => $inventory->warehouse_id,
                'bin_location_id' => $inventory->bin_location_id,
                'batch_id' => null,
                'quantity' => $validated['adjustment_type'] === 'increase' ? $adjustmentAmount : -$adjustmentAmount,
                'movement_type' => 'adjustment',
                'reference_type' => 'stock_adjustment',
                'reference_id' => $inventory->id,
                'notes' => $validated['notes'] ?? 'Inventory quantity adjustment',
                'created_by' => Auth::user()->id,
            ]);
            
            DB::commit();
            
            $inventory->load(['item', 'warehouse', 'binLocation']);
            
            return response()->json([
                'message' => 'Inventory quantity adjusted successfully',
                'inventory' => $inventory
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
                
            return response()->json([
                'message' => 'Failed to adjust inventory quantity',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
