<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\BinLocation;
use App\Services\InventoryManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GoodsReceiptController extends Controller
{
    /**
     * Display a listing of the goods receipts.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('business_id');
        $query = GoodsReceipt::with([
                'supplier:id,name',
                'purchaseOrder:id,po_number',
                'receivedBy:id,name',
                'warehouse:id,name',
                'items' => function ($query) {
                    $query->select('id', 'goods_receipt_id', 'item_id', 'quantity_received', 'quantity_accepted', 'quantity_rejected', 'status')
                          ->with('item:id,item_name');
                }
            ])
            ->where('business_id', $business_id)
            ->orderBy('created_at', 'desc');

        // Handle filters
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('supplier_id') && $request->input('supplier_id') !== 'all') {
            $query->where('supplier_id', $request->input('supplier_id'));
        }
            
        $receipts = $query->paginate(10)->withQueryString();
        $suppliers = Supplier::where('business_id', $business_id)->get(['id', 'name']);

        return Inertia::render('Inventory/Warehouse/GoodsReceipt/Index', [
            'receipts' => $receipts,
            'suppliers' => $suppliers,
            'filters' => $request->only(['status', 'supplier_id', 'search']),
            'statuses' => [
                'pending' => 'Pending',
                'inspecting' => 'Inspecting',
                'completed' => 'Completed',
                'rejected' => 'Rejected'
            ]
        ]);
    }

    /**
     * Show the form for creating a new goods receipt.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $business_id = Auth::user()->business_id;
        $suppliers = Supplier::where('business_id', $business_id)->get(['id', 'name']);
        $purchaseOrders = PurchaseOrder::where('business_id', $business_id)
            ->whereIn('status', [
                PurchaseOrder::STATUS_SENT, 
                PurchaseOrder::STATUS_PARTIAL
            ])
            ->get(['id', 'po_number', 'supplier_id']);
        
        $warehouses = Warehouse::where('business_id', $business_id)->get(['id', 'name']);
        
        return Inertia::render('Inventory/Warehouse/GoodsReceipt/Create', [
            'suppliers' => $suppliers,
            'purchaseOrders' => $purchaseOrders,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Get purchase order items for receipt creation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchaseOrderItems(Request $request)
    {
        $purchaseOrderId = $request->input('purchase_order_id');
        
        $items = PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)
            ->whereRaw('quantity > quantity_received')
            ->with(['item' => function ($query) {
                $query->select('id', 'item_name', 'sku', 'unit');
            }])
            ->get([
                'id', 'item_id', 'quantity', 'quantity_received', 
                'unit_price', 'status', 'unit_of_measure'
            ]);
            
        return response()->json([
            'items' => $items
        ]);
    }

    /**
     * Store a newly created goods receipt in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $business_id = Auth::user()->business_id;
        
        $validated = $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'supplier_reference' => 'nullable|string|max:255',
            'delivery_note_number' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.item_id' => 'required|exists:resource_item,id',
            'items.*.quantity_received' => 'required|numeric|min:0.01',
            'items.*.bin_location_id' => 'nullable|exists:bin_locations,id',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.manufacturing_date' => 'nullable|date',
            'items.*.expiry_date' => 'nullable|date|after_or_equal:manufacturing_date',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();
            
            // Generate receipt number
            $latestReceipt = GoodsReceipt::latest()->first();
            $receiptNumber = 'GR-' . str_pad(($latestReceipt ? intval(substr($latestReceipt->receipt_number, 3)) + 1 : 1), 6, '0', STR_PAD_LEFT);
            
            // Create goods receipt
            $goodsReceipt = GoodsReceipt::create([
                'business_id' => $business_id,
                'receipt_number' => $receiptNumber,
                'purchase_order_id' => $validated['purchase_order_id'],
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'receipt_date' => $validated['receipt_date'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'received_by' => Auth::id(),
                'supplier_reference' => $validated['supplier_reference'] ?? null,
                'delivery_note_number' => $validated['delivery_note_number'] ?? null,
                'carrier' => $validated['carrier'] ?? null,
                'tracking_number' => $validated['tracking_number'] ?? null,
                'total_quantity' => collect($validated['items'])->sum('quantity_received'),
            ]);
            
            // Process items
            foreach ($validated['items'] as $item) {
                $goodsReceiptItem = GoodsReceiptItem::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'] ?? null,
                    'item_id' => $item['item_id'],
                    'quantity_expected' => $item['purchase_order_item_id'] ? PurchaseOrderItem::find($item['purchase_order_item_id'])->quantity : null,
                    'quantity_received' => $item['quantity_received'],
                    'quantity_accepted' => $item['quantity_received'], // Initially set to same as received
                    'batch_number' => $item['batch_number'] ?? null,
                    'lot_number' => $item['lot_number'] ?? null,
                    'manufacturing_date' => $item['manufacturing_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'bin_location_id' => $item['bin_location_id'] ?? null,
                    'status' => 'pending',
                    'unit_cost' => $item['unit_cost'] ?? 0,
                    'unit_of_measure' => $item['unit_of_measure'] ?? null,
                ]);
                
                // Update purchase order item if linked
                if (!empty($item['purchase_order_item_id'])) {
                    $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);
                    $poItem->quantity_received += $item['quantity_received'];
                    
                    // Update status based on received quantity
                    if ($poItem->quantity_received >= $poItem->quantity) {
                        $poItem->status = PurchaseOrderItem::STATUS_RECEIVED;
                    } else if ($poItem->quantity_received > 0) {
                        $poItem->status = PurchaseOrderItem::STATUS_PARTIAL;
                    }
                    
                    $poItem->save();
                }
            }
            
            // Update purchase order status if linked
            if ($validated['purchase_order_id']) {
                $this->updatePurchaseOrderStatus($validated['purchase_order_id']);
            }
            
            DB::commit();
            
            return redirect()->route('purchasing.receipts.show', $goodsReceipt->id)
                ->with('success', 'Goods receipt created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating goods receipt: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified goods receipt.
     *
     * @param  \App\Models\GoodsReceipt  $goodsReceipt
     * @return \Inertia\Response
     */
    public function show(GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        $goodsReceipt->load([
            'supplier:id,name,contact_person,email,phone',
            'warehouse:id,name,address',
            'purchaseOrder:id,po_number,order_date',
            'receivedBy:id,name',
            'inspectedBy:id,name',
            'items' => function($query) {
                $query->with([
                    'item:id,item_name,sku,unit',
                    'purchaseOrderItem:id,quantity,unit_price',
                    'binLocation:id,location_code,name',
                    'inspectedBy:id,name'
                ]);
            }
        ]);
        
        // Get available bin locations for this warehouse
        $binLocations = BinLocation::where('warehouse_id', $goodsReceipt->warehouse_id)
            ->get(['id', 'location_code', 'name']);
        
        return Inertia::render('Inventory/Warehouse/GoodsReceipt/Show', [
            'goodsReceipt' => $goodsReceipt,
            'binLocations' => $binLocations,
            'canInspect' => $goodsReceipt->status === 'pending',
            'canComplete' => $goodsReceipt->status === 'inspecting',
        ]);
    }
    
    /**
     * Mark goods receipt items as inspected and update inventory.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function inspectItems(Request $request, GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($goodsReceipt->status !== 'pending') {
            return redirect()->back()->with('error', 'This receipt is already being inspected or has been completed.');
        }
        
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:goods_receipt_items,id',
            'items.*.quantity_accepted' => 'required|numeric|min:0',
            'items.*.quantity_rejected' => 'required|numeric|min:0',
            'items.*.rejection_reason' => 'nullable|string|required_if:items.*.quantity_rejected,>,0',
            'items.*.bin_location_id' => 'nullable|exists:bin_locations,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $goodsReceipt->status = 'inspecting';
            $goodsReceipt->inspected_by = Auth::id();
            $goodsReceipt->inspected_at = now();
            $goodsReceipt->total_accepted = 0;
            $goodsReceipt->total_rejected = 0;
            
            foreach ($validated['items'] as $itemData) {
                $receiptItem = GoodsReceiptItem::find($itemData['id']);
                
                // Ensure we're updating the correct item
                if ($receiptItem->goods_receipt_id !== $goodsReceipt->id) {
                    continue;
                }
                
                $receiptItem->quantity_accepted = $itemData['quantity_accepted'];
                $receiptItem->quantity_rejected = $itemData['quantity_rejected'];
                $receiptItem->rejection_reason = $itemData['rejection_reason'] ?? null;
                $receiptItem->bin_location_id = $itemData['bin_location_id'] ?? $receiptItem->bin_location_id;
                $receiptItem->status = 'inspecting';
                $receiptItem->inspected_by = Auth::id();
                $receiptItem->inspected_at = now();
                $receiptItem->save();
                
                // Update totals for the receipt
                $goodsReceipt->total_accepted += $itemData['quantity_accepted'];
                $goodsReceipt->total_rejected += $itemData['quantity_rejected'];
            }
            
            $goodsReceipt->save();
            
            DB::commit();
            
            return redirect()->route('purchasing.receipts.show', $goodsReceipt->id)
                ->with('success', 'Items inspected successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating items: ' . $e->getMessage());
        }
    }
    
    /**
     * Complete the goods receipt process and update inventory.
     *
     * @param  \App\Models\GoodsReceipt  $goodsReceipt
     * @return \Illuminate\Http\RedirectResponse
     */
    public function complete(GoodsReceipt $goodsReceipt)
    {
        if ($goodsReceipt->business_id !== Auth::user()->business_id) {
            abort(403, 'Unauthorized action.');
        }
        
        if ($goodsReceipt->status !== 'inspecting') {
            return redirect()->back()->with('error', 'This receipt must be inspected before completion.');
        }
        
        try {
            DB::beginTransaction();
            
            // Use the InventoryManager service to handle inventory creation
            $inventoryManager = app(InventoryManager::class);
            $result = $inventoryManager->createInventoryFromGoodsReceipt($goodsReceipt->id);
            
            if (!$result['status']) {
                DB::rollBack();
                return redirect()->back()->with('error', $result['message']);
            }
            
            // Update purchase order if applicable
            if ($goodsReceipt->purchase_order_id) {
                $this->updatePurchaseOrderStatus($goodsReceipt->purchase_order_id);
            }
            
            DB::commit();
            
            return redirect()->route('purchasing.receipts')
                ->with('success', 'Goods receipt completed and inventory updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error completing goods receipt: ' . $e->getMessage());
        }
    }
    
    /**
     * Update purchase order status based on received items.
     *
     * @param  int  $purchaseOrderId
     * @return void
     */
    private function updatePurchaseOrderStatus($purchaseOrderId)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        $items = $purchaseOrder->items;
        
        if ($items->isEmpty()) {
            return;
        }
        
        $allReceived = true;
        $anyReceived = false;
        
        foreach ($items as $item) {
            if ($item->quantity_received < $item->quantity) {
                $allReceived = false;
            }
            
            if ($item->quantity_received > 0) {
                $anyReceived = true;
            }
        }
        
        // Update purchase order quantities
        $purchaseOrder->total_quantity_received = $items->sum('quantity_received');
        
        // Update status
        if ($allReceived) {
            $purchaseOrder->status = PurchaseOrder::STATUS_RECEIVED;
            $purchaseOrder->delivery_date = now();
        } elseif ($anyReceived) {
            $purchaseOrder->status = PurchaseOrder::STATUS_PARTIAL;
        }
        
        $purchaseOrder->save();
    }

    /**
     * API endpoint to create inventory from goods receipt
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiCreateInventory(Request $request, $id)
    {
        try {
            $goodsReceipt = GoodsReceipt::findOrFail($id);
            
            if ($goodsReceipt->business_id !== Auth::user()->business_id) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            if (!in_array($goodsReceipt->status, ['inspecting', 'pending'])) {
                return response()->json([
                    'success' => false, 
                    'message' => 'This receipt has already been processed'
                ], 400);
            }
            
            // Use the InventoryManager service
            $inventoryManager = app(InventoryManager::class);
            $result = $inventoryManager->createInventoryFromGoodsReceipt($id, ['user_id' => Auth::id()]);
            
            if (!$result['status']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? null
                ], 422);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Inventory created successfully',
                'inventories' => $result['inventories'],
                'batches' => $result['batches'],
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create inventory: ' . $e->getMessage()
            ], 500);
        }
    }
}
