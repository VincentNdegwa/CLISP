<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\Warehouse;
use App\Models\BinLocation;
use App\Models\WarehouseZone;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'business_id' => 'required|string|exists:business,business_id',
            'item_id' => 'required|exists:resource_item,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'bin_location_id' => 'required|exists:bin_locations,id',
            'quantity' => 'required|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'reorder_quantity' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Check if inventory already exists for this item and location
            $inventory = Inventory::where('business_id', $request->business_id)
                ->where('item_id', $request->item_id)
                ->where('warehouse_id', $request->warehouse_id)
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
                'business_id' => $request->business_id,
                'item_id' => $request->item_id,
                'warehouse_id' => $request->warehouse_id,
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
}
