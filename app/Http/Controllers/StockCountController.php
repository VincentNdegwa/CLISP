<?php

namespace App\Http\Controllers;

use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\Warehouse;
use App\Models\WarehouseZone;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\BinLocation;
use App\Models\ResourceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockCountController extends Controller
{
    /**
     * Display a listing of stock counts.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('business_id');
        $query = StockCount::with([
                'warehouse:id,name',
                'zone:id,name',
                'createdBy:id,name',
                'assignedTo:id,name',
                'verifiedBy:id,name'
            ])
            ->where('business_id', $business_id)
            ->orderBy('created_at', 'desc');

        // Handle filters
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('warehouse_id') && $request->input('warehouse_id') !== 'all') {
            $query->where('warehouse_id', $request->input('warehouse_id'));
        }
            
        $stockCounts = $query->paginate(10)->withQueryString();
        $warehouses = Warehouse::where('business_id', $business_id)->get(['id', 'name']);

        return Inertia::render('Inventory/Movements/StockCount/Index', [
            'stockCounts' => $stockCounts,
            'warehouses' => $warehouses,
            'filters' => $request->only(['status', 'warehouse_id', 'search']),
            'statuses' => [
                'draft' => 'Draft',
                'in_progress' => 'In Progress',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ]
        ]);
    }

    /**
     * Show the form for creating a new stock count.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $business_id = Auth::user()->business_id;
        $warehouses = Warehouse::where('business_id', $business_id)->get(['id', 'name']);
        $zones = WarehouseZone::whereIn('warehouse_id', $warehouses->pluck('id'))->get(['id', 'name', 'warehouse_id']);
        $users = DB::table('business_user')
            ->join('users', 'business_user.user_id', '=', 'users.id')
            ->where('business_user.business_id', $business_id)
            ->select('users.id', 'users.name')
            ->get();
            
        $items = ResourceItem::where('business_id', $business_id)
            ->where('is_inventory_tracked', true)
            ->orderBy('item_name')
            ->get(['id', 'item_name', 'sku']);
            
        return Inertia::render('Inventory/Movements/StockCount/Create', [
            'warehouses' => $warehouses,
            'zones' => $zones,
            'users' => $users,
            'items' => $items,
            'countTypes' => [
                'full' => 'Full Inventory Count',
                'cycle' => 'Cycle Count',
                'spot' => 'Spot Check',
                'zone' => 'Zone Count'
            ]
        ]);
    }

    /**
     * Store a newly created stock count in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $business_id = Auth::user()->business_id;
        
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'zone_id' => 'nullable|exists:warehouse_zones,id',
            'count_date' => 'required|date',
            'count_type' => 'required|in:full,cycle,spot,zone',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'items' => 'required_if:count_type,spot|array',
            'items.*' => 'exists:resource_item,id',
        ]);

        try {
            DB::beginTransaction();
            
            // Generate count number
            $latestCount = StockCount::latest()->first();
            $countNumber = 'SC-' . str_pad(($latestCount ? intval(substr($latestCount->count_number, 3)) + 1 : 1), 6, '0', STR_PAD_LEFT);
            
            $stockCount = StockCount::create([
                'business_id' => $business_id,
                'count_number' => $countNumber,
                'warehouse_id' => $validated['warehouse_id'],
                'zone_id' => $validated['zone_id'],
                'count_date' => $validated['count_date'],
                'count_type' => $validated['count_type'],
                'status' => 'draft',
                'description' => $validated['description'],
                'created_by' => Auth::id(),
                'assigned_to' => $validated['assigned_to'],
            ]);
            
            // Determine which items to include in the count
            $itemQuery = Inventory::where('inventories.warehouse_id', $validated['warehouse_id'])
                ->join('resource_item', 'inventories.item_id', '=', 'resource_item.id')
                ->where('resource_item.business_id', $business_id)
                ->select('inventories.*');
                
            if ($validated['zone_id']) {
                $itemQuery->whereHas('binLocation', function($q) use ($validated) {
                    $q->where('zone_id', $validated['zone_id']);
                });
            }
            
            if ($validated['count_type'] === 'spot' && !empty($validated['items'])) {
                $itemQuery->whereIn('inventories.item_id', $validated['items']);
            }
            
            $inventoryItems = $itemQuery->get();
            
            // Create stock count items for each inventory
            foreach ($inventoryItems as $inventoryItem) {
                // Get all batches for this inventory
                $batches = InventoryBatch::where('inventory_id', $inventoryItem->id)
                    ->where('quantity', '>', 0)
                    ->get();
                    
                if ($batches->isEmpty()) {
                    // Create a stock count item without batch
                    StockCountItem::create([
                        'stock_count_id' => $stockCount->id,
                        'item_id' => $inventoryItem->item_id,
                        'bin_location_id' => $inventoryItem->bin_location_id,
                        'expected_quantity' => $inventoryItem->quantity,
                        'unit_cost' => 0, // Will be updated during counting
                        'status' => 'pending',
                    ]);
                } else {
                    // Create a stock count item for each batch
                    foreach ($batches as $batch) {
                        StockCountItem::create([
                            'stock_count_id' => $stockCount->id,
                            'item_id' => $inventoryItem->item_id,
                            'batch_id' => $batch->id,
                            'bin_location_id' => $inventoryItem->bin_location_id,
                            'expected_quantity' => $batch->quantity,
                            'unit_cost' => $batch->cost_price ?? 0,
                            'status' => 'pending',
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('logistics.counts.show', $stockCount->id)
                ->with('success', 'Stock count created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating stock count: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified stock count.
     *
     * @param  \App\Models\StockCount  $stockCount
     * @return \Inertia\Response
     */
    public function show(StockCount $stockCount)
    {
        if ($stockCount->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $stockCount->load([
            'warehouse:id,name,address',
            'zone:id,name',
            'createdBy:id,name',
            'assignedTo:id,name',
            'verifiedBy:id,name',
            'items' => function($query) {
                $query->with([
                    'item:id,item_name,sku,unit',
                    'batch:id,batch_number,lot_number,expiry_date',
                    'binLocation:id,location_code,name',
                    'countedBy:id,name',
                    'verifiedBy:id,name'
                ]);
            }
        ]);
        
        // Get available bin locations for this warehouse
        $binLocations = BinLocation::where('warehouse_id', $stockCount->warehouse_id)
            ->get(['id', 'location_code', 'name']);
        
        return Inertia::render('Inventory/Movements/StockCount/Show', [
            'stockCount' => $stockCount,
            'binLocations' => $binLocations,
            'canCount' => in_array($stockCount->status, ['draft', 'in_progress']),
            'canVerify' => $stockCount->status === 'in_progress' && $stockCount->items->every(fn($item) => $item->status === 'counted'),
            'canAdjust' => $stockCount->status === 'completed' && !$stockCount->adjustments_created,
        ]);
    }
    
    /**
     * Update counted quantities for stock count items.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\StockCount  $stockCount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCounts(Request $request, StockCount $stockCount)
    {
        if ($stockCount->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        if (!in_array($stockCount->status, ['draft', 'in_progress'])) {
            return redirect()->back()->with('error', 'This stock count cannot be updated.');
        }
        
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:stock_count_items,id',
            'items.*.counted_quantity' => 'required|numeric|min:0',
            'items.*.bin_location_id' => 'nullable|exists:bin_locations,id',
            'items.*.notes' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            if ($stockCount->status === 'draft') {
                $stockCount->status = 'in_progress';
                $stockCount->save();
            }
            
            foreach ($validated['items'] as $itemData) {
                $countItem = StockCountItem::find($itemData['id']);
                
                // Ensure we're updating the correct item
                if ($countItem->stock_count_id !== $stockCount->id) {
                    continue;
                }
                
                $countItem->counted_quantity = $itemData['counted_quantity'];
                $countItem->bin_location_id = $itemData['bin_location_id'] ?? $countItem->bin_location_id;
                $countItem->notes = $itemData['notes'] ?? null;
                $countItem->status = 'counted';
                $countItem->counted_by = Auth::id();
                $countItem->counted_at = now();
                
                // Calculate discrepancy
                $countItem->discrepancy = $countItem->counted_quantity - $countItem->expected_quantity;
                
                if ($countItem->expected_quantity > 0) {
                    $countItem->discrepancy_percentage = ($countItem->discrepancy / $countItem->expected_quantity) * 100;
                } else {
                    $countItem->discrepancy_percentage = $countItem->counted_quantity > 0 ? 100 : 0;
                }
                
                // Calculate discrepancy value
                $countItem->discrepancy_value = $countItem->discrepancy * $countItem->unit_cost;
                
                // Flag for recount if discrepancy is significant
                if (abs($countItem->discrepancy_percentage) > 10) {
                    $countItem->requires_recount = true;
                }
                
                $countItem->save();
            }
            
            DB::commit();
            
            return redirect()->route('logistics.counts.show', $stockCount->id)
                ->with('success', 'Counts updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating counts: ' . $e->getMessage());
        }
    }
    
    /**
     * Verify a stock count and mark it as complete.
     *
     * @param  \App\Models\StockCount  $stockCount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(StockCount $stockCount)
    {
        if ($stockCount->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($stockCount->status !== 'in_progress' || !$stockCount->items->every(fn($item) => $item->status === 'counted')) {
            return redirect()->back()->with('error', 'All items must be counted before verification.');
        }
        
        try {
            DB::beginTransaction();
            
            $stockCount->status = 'completed';
            $stockCount->verified_by = Auth::id();
            $stockCount->verified_at = now();
            
            // Calculate summary statistics
            $stockCount->total_items_counted = $stockCount->items->count();
            $stockCount->total_discrepancies = $stockCount->items->sum('discrepancy');
            $stockCount->total_discrepancy_value = $stockCount->items->sum('discrepancy_value');
            
            // Calculate accuracy percentage
            $totalExpected = $stockCount->items->sum('expected_quantity');
            $totalDiscrepancy = abs($stockCount->total_discrepancies);
            
            if ($totalExpected > 0) {
                $stockCount->accuracy_percentage = 100 - (($totalDiscrepancy / $totalExpected) * 100);
            } else if ($totalDiscrepancy == 0) {
                $stockCount->accuracy_percentage = 100;
            } else {
                $stockCount->accuracy_percentage = 0;
            }
            
            $stockCount->save();
            
            // Mark all stock count items as verified
            foreach ($stockCount->items as $item) {
                $item->status = 'verified';
                $item->verified_by = Auth::id();
                $item->verified_at = now();
                $item->save();
            }
            
            DB::commit();
            
            return redirect()->route('logistics.counts.show', $stockCount->id)
                ->with('success', 'Stock count verified and completed successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error verifying stock count: ' . $e->getMessage());
        }
    }
    
    /**
     * Create inventory adjustments based on stock count.
     *
     * @param  \App\Models\StockCount  $stockCount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createAdjustment(StockCount $stockCount)
    {
        if ($stockCount->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($stockCount->status !== 'completed' || $stockCount->adjustments_created) {
            return redirect()->back()->with('error', 'Adjustments cannot be created for this stock count.');
        }
        
        try {
            DB::beginTransaction();
            
            // Create adjustment
            $adjustment = InventoryAdjustment::create([
                'business_id' => $stockCount->business_id,
                'warehouse_id' => $stockCount->warehouse_id,
                'adjustment_number' => 'ADJ-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                'adjustment_date' => now(),
                'reason' => 'Stock count adjustment: ' . $stockCount->count_number,
                'notes' => 'Automatically created from stock count ' . $stockCount->count_number,
                'created_by' => Auth::id(),
                'status' => 'completed',
                'reference_type' => 'StockCount',
                'reference_id' => $stockCount->id,
            ]);
            
            // Create adjustment items
            foreach ($stockCount->items as $countItem) {
                // Only create adjustment if there's a discrepancy
                if ($countItem->discrepancy != 0) {
                    $adjustmentItem = InventoryAdjustmentItem::create([
                        'adjustment_id' => $adjustment->id,
                        'item_id' => $countItem->item_id,
                        'batch_id' => $countItem->batch_id,
                        'warehouse_id' => $stockCount->warehouse_id,
                        'bin_location_id' => $countItem->bin_location_id,
                        'quantity_before' => $countItem->expected_quantity,
                        'quantity_after' => $countItem->counted_quantity,
                        'adjustment_quantity' => $countItem->discrepancy,
                        'unit_cost' => $countItem->unit_cost,
                        'total_cost' => $countItem->discrepancy_value,
                        'reason' => 'Stock count adjustment',
                        'notes' => $countItem->notes,
                    ]);
                    
                    // Update inventory
                    if ($countItem->batch_id) {
                        // Update batch quantity
                        $batch = InventoryBatch::find($countItem->batch_id);
                        if ($batch) {
                            $batch->quantity = $countItem->counted_quantity;
                            $batch->save();
                        }
                    }
                    
                    // Update inventory record
                    $inventory = Inventory::where('item_id', $countItem->item_id)
                        ->where('warehouse_id', $stockCount->warehouse_id)
                        ->first();
                        
                    if ($inventory) {
                        // Recalculate total quantity from all batches
                        $batchSum = InventoryBatch::where('inventory_id', $inventory->id)
                            ->sum('quantity');
                        
                        // Update inventory quantity
                        $inventory->quantity = $batchSum;
                        
                        // Update inventory status based on new quantity
                        if ($inventory->quantity <= 0) {
                            $inventory->status = Inventory::STATUS_OUT_OF_STOCK;
                        } else if ($inventory->quantity < $inventory->reorder_point) {
                            $inventory->status = Inventory::STATUS_LOW_STOCK;
                        } else {
                            $inventory->status = Inventory::STATUS_IN_STOCK;
                        }
                        
                        $inventory->save();
                    }
                    
                    // Mark the count item as adjusted
                    $countItem->adjustment_created = true;
                    $countItem->save();
                }
            }
            
            // Update stock count
            $stockCount->adjustments_created = true;
            $stockCount->adjustment_id = $adjustment->id;
            $stockCount->save();
            
            DB::commit();
            
            return redirect()->route('logistics.counts.show', $stockCount->id)
                ->with('success', 'Inventory adjustments created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating adjustments: ' . $e->getMessage());
        }
    }
}
