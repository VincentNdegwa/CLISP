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
use App\Services\InventoryManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    protected $inventoryManager;

    public function __construct(InventoryManager $inventoryManager)
    {
        $this->inventoryManager = $inventoryManager;
    }

    public function resources()
    {
        return Inertia::render('Inventory/Resources/Index');
    }

    public function inventory(){
        $statusText = Inventory::$statusText;
        $statusClasses = Inventory::$statusClass;
        $statusOptions = [
            ['label' => 'All', 'value' => null]
        ];

        foreach (Inventory::$statusText as $value => $label) {
            $statusOptions[] = [
                'label' => $label,
                'value' => $value
            ];
        }
        return Inertia::render('Inventory/Inventory/Index',[
            'statuses' => $statusOptions,
            'statusClasses' => $statusClasses,
        ]);
    }

    public function apiIndex(Request $request)
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
    public function index(){
        return Inertia::render('Inventory/Inventory/Index');
    }
    public function view(Request $request, $id)
    {
        return Inertia::render('Inventory/Inventory/Show', [
            'inventoryId'=> $id
        ]);
    }

    public function show($id)
    {
        $inventory = Inventory::with(['item', 'warehouse', 'binLocation', 'batches','stockAdjustments'])
            ->findOrFail($id);

        return response()->json($inventory);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:resource_item,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'bin_location_id' => 'required|exists:bin_locations,id',
            'quantity' => 'required|numeric|min:0',
            'reorder_point' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);

        $inventoryData = [
            'item_id' => $validated['item_id'],
            'warehouse_id' => $validated['warehouse_id'],
            'bin_location_id' => $validated['bin_location_id'],
            'quantity' => $validated['quantity'],
            'business_id' => $warehouse->business_id,
            'reorder_point' => $validated['reorder_point'] ?? null,
            'min_stock_level' => $validated['min_stock_level'] ?? null,
            'max_stock_level' => $validated['max_stock_level'] ?? null,
            'notes' => $validated['notes'] ?? 'Initial inventory setup',
            'user_id' => Auth::id(),
        ];

        $result = $this->inventoryManager->createOrUpdateInventory($inventoryData);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => ['general' => [$result['error'] ?? 'Unknown error']]
            ], 500);
        }

        return response()->json([
            'message' => $result['message'],
            'inventory' => $result['inventory']
        ], $result['is_new'] ? 201 : 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'sometimes|numeric|min:0',
            'bin_location_id' => 'sometimes|exists:bin_locations,id',
            'reorder_point' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|numeric|min:0',
            'max_stock_level' => 'nullable|numeric|min:0',
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

            if ($request->has('min_stock_level')) {
                $inventory->min_stock_level = $request->min_stock_level;
            }
            if ($request->has('max_stock_level')) {
                $inventory->max_stock_level = $request->max_stock_level;
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
            'batch_id' => 'nullable|exists:inventory_batches,id',
        ]);

        $inventory = Inventory::findOrFail($id);

        $adjustmentData = [
            'inventory_id' => $id,
            'adjustment_type' => $validated['adjustment_type'],
            'quantity' => $validated['quantity'],
            'reference_type' => 'stock_adjustment',
            'reference_id' => null,
            'reason_id' => $validated['reason_id'],
            'notes' => $validated['notes'] ?? null,
            'batch_id' => $validated['batch_id'] ?? null,
            'user_id' => Auth::id(),
        ];

        $result = $this->inventoryManager->adjustQuantity($adjustmentData);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? ['general' => [$result['error'] ?? 'Unknown error']]
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'inventory' => $result['inventory']
        ]);
    }

    public function transferStock(Request $request)
    {
        $validated = $request->validate([
            'source_inventory_id' => 'required|exists:inventories,id',
            'destination_inventory_id' => 'nullable|exists:inventories,id',
            'destination_warehouse_id' => 'required_without:destination_inventory_id|exists:warehouses,id',
            'destination_bin_location_id' => 'nullable|exists:bin_locations,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
            'batch_id' => 'nullable|exists:inventory_batches,id',
        ]);

        $transferData = [
            'source_inventory_id' => $validated['source_inventory_id'],
            'destination_inventory_id' => $validated['destination_inventory_id'] ?? null,
            'destination_warehouse_id' => $validated['destination_warehouse_id'] ?? null,
            'destination_bin_location_id' => $validated['destination_bin_location_id'] ?? null,
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'] ?? 'Stock transfer',
            'batch_id' => $validated['batch_id'] ?? null,
            'user_id' => Auth::id(),
            'reference_type' => 'manual_transfer',
            'reference_id' => null,
        ];

        $result = $this->inventoryManager->transferStock($transferData);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? ['general' => [$result['error'] ?? 'Unknown error']]
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'transfer' => $result['transfer']
        ]);
    }

    public function processBatch($inventoryId, Request $request)
    {
        $operation = $request->input('operation');

        $rules = [
            'operation' => 'required|in:create,adjust,expire,damage',
            'adjust_inventory' => 'sometimes|boolean',
            'notes' => 'nullable|string',
        ];

        if ($operation === 'create') {
            $rules['quantity'] = 'required|numeric|min:0';
            $rules['batch_data'] = 'required|array';
            $rules['batch_data.batch_number'] = 'required|string';
            $rules['reason_id'] = 'required|exists:stock_adjustment_reasons,id';
        }
        elseif ($operation === 'adjust') {
            $rules['batch_id'] = 'required|exists:inventory_batches,id';
            $rules['quantity'] = 'required|numeric|min:0';
            $rules['reason_id'] = 'required|exists:stock_adjustment_reasons,id';
        }
        elseif ($operation === 'expire') {
            $rules['batch_id'] = 'required|exists:inventory_batches,id';
            $rules['reason_id'] = 'required|exists:stock_adjustment_reasons,id';
        }
        elseif ($operation === 'damage') {
            $rules['batch_id'] = 'required|exists:inventory_batches,id';
            $rules['damaged_quantity'] = 'required|numeric|min:0.01';
            $rules['reason_id'] = 'required|exists:stock_adjustment_reasons,id';
        }

        $validated = $request->validate($rules);

        // Rest of your code remains the same
        $batchData = [
            'inventory_id' => $inventoryId,
            'operation' => $validated['operation'],
            'quantity' => $validated['quantity'] ?? 0,
            'batch_id' => $validated['batch_id'] ?? null,
            'batch_data' => $validated['batch_data'] ?? [],
            'damaged_quantity' => $validated['damaged_quantity'] ?? null,
            'reason_id' => $validated['reason_id'] ?? null,
            'adjust_inventory' => $validated['adjust_inventory'] ?? true,
            'user_id' => Auth::id(),
        ];

        $result = $this->inventoryManager->processBatchOperation($batchData);

        if (!$result['status']) {
            return response()->json([
                'message' => $result['message'],
                'errors' => $result['errors'] ?? ['general' => [$result['error'] ?? 'Unknown error']]
            ], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'batch' => $result['batch'],
            'inventory' => $result['inventory']
        ]);
    }

    public function getBatches($inventoryId)
    {
        try {
            $batches = InventoryBatch::where('inventory_id', $inventoryId)
                ->with(['inventory', 'supplier', 'purchaseOrder'])
                ->get();

            return response()->json($batches);
        }catch (\Exception $exception){
            return response()->json([
                'message' => 'Error fetching batches: ' . $exception->getMessage(),
                'errors' => ['general' => [$exception->getMessage()]]
            ], 500);
        }
    }
}
