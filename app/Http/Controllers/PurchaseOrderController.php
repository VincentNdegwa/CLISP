<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ResourceItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the purchase orders.
     */
    public function index(Request $request)
    {
        $business = $request->session()->get('business_id');
        $query = PurchaseOrder::with([
                'supplier:id,name,email,phone',
                'creator:id,name',
                'items' => function ($query) {
                    $query->select('id', 'purchase_order_id', 'item_id', 'quantity', 'unit_price', 'total_price', 'status')
                          ->with('item:id,item_name');
                }
            ])
            ->where('business_id', $business)
            ->orderBy('created_at', 'desc');

        // Handle filters
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('supplier_id') && $request->input('supplier_id') !== 'all') {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where('po_number', 'like', "%{$search}%")
                      ->orWhereHas('supplier', function ($query) use ($search) {
                          $query->where('name', 'like', "%{$search}%");
                      });
            });
        }

        $purchaseOrders = $query->paginate(10);
        $suppliers = Supplier::where('business_id', $business)->select('id', 'name')->get();

        return Inertia::render('Purchasing/Orders/Index', [
            'purchaseOrders' => $purchaseOrders,
            'suppliers' => $suppliers,
            'filters' => $request->only(['status', 'supplier_id', 'search']),
            'statuses' => PurchaseOrder::$statusText,
        ]);
    }

    /**
     * Show the form for creating a new purchase order.
     */
    public function create(Request $request)
    {
        $business = $request->session()->get('business_id');
        $suppliers = Supplier::where('business_id', $business)->select('id', 'name', 'email', 'phone')->get();
        $items = ResourceItem::where('business_id', $business)
            ->select('id', 'item_name', 'price', 'unit')
            ->get();

        return Inertia::render('Purchasing/Orders/Create', [
            'suppliers' => $suppliers,
            'items' => $items,
            'nextPoNumber' => $this->generateNextPoNumber($business),
        ]);
    }


    /**
     * Display the specified purchase order.
     */
    public function show(Request $request, $id)
    {
        $business = $request->session()->get('business_id');
        
        $purchaseOrder = PurchaseOrder::with([
                'supplier',
                'items' => function ($query) {
                    $query->with('item');
                },
                'creator',
                'approver',
                'receipts' => function ($query) {
                    $query->with(['receivedBy', 'items']);
                }
            ])
            ->where('business_id', $business)
            ->findOrFail($id);
            
        return Inertia::render('Inventory/PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
            'statusText' => PurchaseOrder::$statusText,
            'statusClass' => PurchaseOrder::$statusClass,
            'canEdit' => $purchaseOrder->status === PurchaseOrder::STATUS_DRAFT,
            'canApprove' => $purchaseOrder->status === PurchaseOrder::STATUS_SUBMITTED && Auth::user()->can('approve_purchase_orders'),
            'canReceive' => in_array($purchaseOrder->status, [
                PurchaseOrder::STATUS_SENT,
                PurchaseOrder::STATUS_PARTIAL
            ])
        ]);
    }

    /**
     * Show the form for editing the specified purchase order.
     */
    public function edit(Request $request, $id)
    {
        $business = $request->session()->get('business_id');
        
        $purchaseOrder = PurchaseOrder::with(['items.item', 'supplier'])
            ->where('business_id', $business)
            ->findOrFail($id);
            
        // Only draft purchase orders can be edited
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return redirect()->route('purchase-orders.show', $id)
                ->with('error', 'Only draft purchase orders can be edited.');
        }
        
        $suppliers = Supplier::where('business_id', $business)
            ->select('id', 'name', 'email', 'phone')
            ->get();
            
        $items = ResourceItem::where('business_id', $business)
            ->select('id', 'item_name', 'price', 'unit')
            ->get();
            
        return Inertia::render('Inventory/PurchaseOrders/Edit', [
            'purchaseOrder' => $purchaseOrder,
            'suppliers' => $suppliers,
            'items' => $items,
        ]);
    }

        // update method removed as we're using API routes for data operations

        // updateStatus method removed as we're using API routes for data operations

        // destroy method removed as we're using API routes for data operations

    /**
     * API: Cancel a purchase order.
     */
    public function apiCancel(Request $request, $id)
    {
        $business = $request->user()->business_id ?? $request->business_id;
        
        $purchaseOrder = PurchaseOrder::where('business_id', $business)
            ->findOrFail($id);
            
        // Cannot cancel received purchase orders
        if (in_array($purchaseOrder->status, [PurchaseOrder::STATUS_PARTIAL, PurchaseOrder::STATUS_RECEIVED])) {
            return response()->json([
                'message' => 'Cannot cancel purchase orders that have been received',
            ], 422);
        }
        
        // Validate request
        $validatedData = $request->validate([
            'notes' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            $oldStatus = $purchaseOrder->status;
            $newStatus = PurchaseOrder::STATUS_CANCELLED;
            
            $updateData = [
                'status' => $newStatus,
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()->id ?? null,
            ];
            
            if ($validatedData['notes'] ?? false) {
                $notes = $purchaseOrder->notes ?? '';
                $notes .= "\n" . now()->format('Y-m-d H:i') . " - Status changed from " . 
                    PurchaseOrder::$statusText[$oldStatus] . " to " . 
                    PurchaseOrder::$statusText[$newStatus] . ": " . $validatedData['notes'];
                $updateData['notes'] = trim($notes);
            }
            
            $purchaseOrder->update($updateData);
            
            DB::commit();
            
            // Load relationships for the response
            $purchaseOrder->load([
                'supplier:id,name,email,phone',
                'creator:id,name',
                'approver:id,name',
                'items' => function ($query) {
                    $query->with('item:id,item_name');
                }
            ]);
            
            return response()->json([
                'message' => 'Purchase order cancelled successfully',
                'purchase_order' => $purchaseOrder
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel purchase order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to cancel purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate the next sequential PO number
     */
    protected function generateNextPoNumber($businessId)
    {
        $business = Business::find($businessId);
        $businessCode = strtoupper(substr($business->business_name, 0, 3));
        $latestPO = PurchaseOrder::where('business_id', $businessId)
            ->where('po_number', 'like', "PO-{$businessCode}-%")
            ->orderBy('po_number', 'desc')
            ->first();
            
        if (!$latestPO) {
            return "PO-{$businessCode}-001";
        }
        
        preg_match('/PO-[A-Z]+-(\d+)/', $latestPO->po_number, $matches);
        if (!isset($matches[1])) {
            return "PO-{$businessCode}-001";
        }
        
        $nextNumber = str_pad(intval($matches[1]) + 1, 3, '0', STR_PAD_LEFT);
        return "PO-{$businessCode}-{$nextNumber}";
    }

    /**
     * API: Display a listing of the purchase orders.
     */
    public function apiIndex(Request $request)
    {
        $business = $request->user()->business_id ?? $request->business_id;
        $query = PurchaseOrder::with([
                'supplier:id,name,email,phone',
                'creator:id,name',
                'items' => function ($query) {
                    $query->select('id', 'purchase_order_id', 'item_id', 'quantity', 'unit_price', 'total_price', 'status')
                          ->with('item:id,item_name');
                }
            ])
            ->where('business_id', $business)
            ->orderBy('created_at', 'desc');

        // Handle filters
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('supplier_id') && $request->input('supplier_id') !== 'all') {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where('po_number', 'like', "%{$search}%")
                      ->orWhereHas('supplier', function ($query) use ($search) {
                          $query->where('name', 'like', "%{$search}%");
                      });
            });
        }

        $perPage = $request->input('rows', 10);
        $purchaseOrders = $query->paginate($perPage);
        
        return response()->json($purchaseOrders);
    }

    /**
     * API: Store a newly created purchase order in storage.
     */
    public function apiStore(Request $request)
    {
        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_number' => 'required|string|max:255|unique:purchase_orders,po_number',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:resource_item,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.unit_of_measure' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
        ]);

        $business = $request->user()->business_id ?? $request->business_id;
        
        try {
            DB::beginTransaction();
            
            // Calculate totals
            $totalQuantity = 0;
            $totalAmount = 0;
            foreach ($validatedData['items'] as $item) {
                $totalQuantity += $item['quantity'];
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }
            
            // Add shipping cost if present
            if (isset($validatedData['shipping_cost']) && $validatedData['shipping_cost'] > 0) {
                $totalAmount += $validatedData['shipping_cost'];
            }
            
            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'business_id' => $business,
                'supplier_id' => $validatedData['supplier_id'],
                'po_number' => $validatedData['po_number'],
                'order_date' => $validatedData['order_date'],
                'expected_delivery_date' => $validatedData['expected_delivery_date'] ?? null,
                'status' => PurchaseOrder::STATUS_DRAFT,
                'payment_terms' => $validatedData['payment_terms'] ?? null,
                'shipping_method' => $validatedData['shipping_method'] ?? null,
                'shipping_cost' => $validatedData['shipping_cost'] ?? 0,
                'total_amount' => $totalAmount,
                'total_quantity_ordered' => $totalQuantity,
                'notes' => $validatedData['notes'] ?? null,
                'created_by' => $request->user()->id ?? null,
            ]);
            
            // Create purchase order items
            foreach ($validatedData['items'] as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                
                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                    'unit_of_measure' => $item['unit_of_measure'] ?? null,
                    'description' => $item['description'] ?? null,
                ]);
            }
            
            DB::commit();
            
            // Load relationships for the response
            $purchaseOrder->load([
                'supplier:id,name,email,phone',
                'creator:id,name',
                'items' => function ($query) {
                    $query->with('item:id,item_name');
                }
            ]);
            
            return response()->json([
                'message' => 'Purchase order created successfully',
                'purchase_order' => $purchaseOrder
            ], 201);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create purchase order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to create purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Display the specified purchase order.
     */
    public function apiShow(Request $request, $id)
    {
        $business = $request->user()->business_id ?? $request->business_id;
        
        $purchaseOrder = PurchaseOrder::with([
                'supplier',
                'items' => function ($query) {
                    $query->with('item');
                },
                'creator',
                'approver',
                'receipts' => function ($query) {
                    $query->with(['receivedBy', 'items']);
                }
            ])
            ->where('business_id', $business)
            ->findOrFail($id);
            
        return response()->json($purchaseOrder);
    }

    /**
     * API: Update the specified purchase order in storage.
     */
    public function apiUpdate(Request $request, $id)
    {
        $business = $request->user()->business_id ?? $request->business_id;
        
        $purchaseOrder = PurchaseOrder::where('business_id', $business)
            ->findOrFail($id);
            
        // Only draft purchase orders can be updated
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return response()->json([
                'message' => 'Only draft purchase orders can be updated',
            ], 422);
        }
        
        $validatedData = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'po_number' => 'required|string|max:255|unique:purchase_orders,po_number,' . $id,
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_order_items,id',
            'items.*.item_id' => 'required|exists:resource_item,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.unit_of_measure' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Calculate totals
            $totalQuantity = 0;
            $totalAmount = 0;
            foreach ($validatedData['items'] as $item) {
                $totalQuantity += $item['quantity'];
                $totalAmount += $item['quantity'] * $item['unit_price'];
            }
            
            // Add shipping cost if present
            if (isset($validatedData['shipping_cost']) && $validatedData['shipping_cost'] > 0) {
                $totalAmount += $validatedData['shipping_cost'];
            }
            
            // Update purchase order
            $purchaseOrder->update([
                'supplier_id' => $validatedData['supplier_id'],
                'po_number' => $validatedData['po_number'],
                'order_date' => $validatedData['order_date'],
                'expected_delivery_date' => $validatedData['expected_delivery_date'] ?? null,
                'payment_terms' => $validatedData['payment_terms'] ?? null,
                'shipping_method' => $validatedData['shipping_method'] ?? null,
                'shipping_cost' => $validatedData['shipping_cost'] ?? 0,
                'total_amount' => $totalAmount,
                'total_quantity_ordered' => $totalQuantity,
                'notes' => $validatedData['notes'] ?? null,
            ]);
            
            // Handle items
            $existingItemIds = [];
            
            foreach ($validatedData['items'] as $itemData) {
                $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
                
                if (isset($itemData['id'])) {
                    // Update existing item
                    $item = PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)
                        ->where('id', $itemData['id'])
                        ->first();
                        
                    if ($item) {
                        $item->update([
                            'item_id' => $itemData['item_id'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'total_price' => $totalPrice,
                            'unit_of_measure' => $itemData['unit_of_measure'] ?? null,
                            'description' => $itemData['description'] ?? null,
                        ]);
                        
                        $existingItemIds[] = $item->id;
                    }
                } else {
                    // Create new item
                    $item = PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $totalPrice,
                        'unit_of_measure' => $itemData['unit_of_measure'] ?? null,
                        'description' => $itemData['description'] ?? null,
                    ]);
                    
                    $existingItemIds[] = $item->id;
                }
            }
            
            // Delete items not present in the update
            PurchaseOrderItem::where('purchase_order_id', $purchaseOrder->id)
                ->whereNotIn('id', $existingItemIds)
                ->delete();
            
            DB::commit();
            
            // Load relationships for the response
            $purchaseOrder->load([
                'supplier:id,name,email,phone',
                'creator:id,name',
                'items' => function ($query) {
                    $query->with('item:id,item_name');
                }
            ]);
            
            return response()->json([
                'message' => 'Purchase order updated successfully',
                'purchase_order' => $purchaseOrder
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update purchase order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to update purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Remove the specified purchase order from storage.
     */
    public function apiDestroy(Request $request, $id)
    {
        $business = $request->user()->business_id ?? $request->business_id;
        
        $purchaseOrder = PurchaseOrder::where('business_id', $business)
            ->findOrFail($id);
            
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return response()->json([
                'message' => 'Only draft purchase orders can be deleted',
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            PurchaseOrderItem::where('purchase_order_id', $id)->delete();
            $purchaseOrder->delete();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Purchase order deleted successfully'
            ]);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete purchase order: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to delete purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
