<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\InventoryAllocation;
use App\Models\InventoryBatch;
use App\Models\ResourceItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of sales orders.
     */
    public function index(Request $request)
    {
        $business = $request->session()->get('business_id');
        $query = SalesOrder::with([
                'customer:id,full_names,email,phone',
                'creator:id,name',
                'items' => function ($query) {
                    $query->select('id', 'sales_order_id', 'item_id', 'quantity', 'unit_price', 'total_price', 'status')
                          ->with('item:id,item_name');
                }
            ])
            ->where('business_id', $business)
            ->orderBy('created_at', 'desc');

        // Handle filters
        if ($request->has('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('customer_id') && $request->input('customer_id') !== 'all') {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($query) use ($search) {
                          $query->where('full_names', 'like', "%{$search}%");
                      });
            });
        }

        $salesOrders = $query->paginate(10);
        $customers = Customer::where('business_id', $business)->select('id', 'full_names')->get();

        return Inertia::render('Inventory/Sales/SalesOrder/Index', [
            'salesOrders' => $salesOrders,
            'customers' => $customers,
            'filters' => $request->only(['status', 'customer_id', 'search']),
            'statuses' => SalesOrder::$statusText,
            'paymentStatuses' => SalesOrder::$paymentStatusText,
            'fulfillmentStatuses' => SalesOrder::$fulfillmentStatusText,
        ]);
    }

    /**
     * Show the form for creating a new sales order.
     */
    public function create(Request $request)
    {
        $business = $request->session()->get('business_id');
        $customers = Customer::where('business_id', $business)
            ->select('id', 'full_names', 'email', 'phone', 'address', 'city', 'state', 'country', 'postal_code')
            ->get();
            
        $items = ResourceItem::whereHas('inventories', function ($query) use ($business) {
                $query->where('business_id', $business)
                      ->where('quantity', '>', 0);
            })
            ->where('business_id', $business)
            ->select('id', 'item_name', 'price', 'unit')
            ->with(['inventories' => function ($query) {
                $query->where('quantity', '>', 0)
                      ->with(['warehouse:id,name', 'binLocation:id,name', 'batches' => function ($query) {
                          $query->where('quantity', '>', 0)
                                ->where('status', 'available');
                      }]);
            }])
            ->get();

        $warehouses = Warehouse::where('business_id', $business)
            ->select('id', 'name')
            ->with('binLocations:id,warehouse_id,name')
            ->get();

        return Inertia::render('Inventory/Sales/Create', [
            'customers' => $customers,
            'items' => $items,
            'warehouses' => $warehouses,
            'nextOrderNumber' => $this->generateNextOrderNumber($business),
        ]);
    }

    /**
     * Store a newly created sales order in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_number' => 'required|string|max:255|unique:sales_orders,order_number',
            'order_date' => 'required|date',
            'required_date' => 'nullable|date|after_or_equal:order_date',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_cost' => 'nullable|numeric|min:0',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:255',
            'shipping_postal_code' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_country' => 'nullable|string|max:255',
            'billing_postal_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:resource_item,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.inventory_id' => 'nullable|exists:inventories,id',
            'items.*.inventory_batch_id' => 'nullable|exists:inventory_batches,id',
            'items.*.unit_of_measure' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
        ]);

        $business = $request->session()->get('business_id');
        
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
            
            // Create sales order
            $salesOrder = SalesOrder::create([
                'business_id' => $business,
                'customer_id' => $validatedData['customer_id'],
                'order_number' => $validatedData['order_number'],
                'order_date' => $validatedData['order_date'],
                'required_date' => $validatedData['required_date'] ?? null,
                'status' => SalesOrder::STATUS_DRAFT,
                'payment_status' => SalesOrder::PAYMENT_PENDING,
                'fulfillment_status' => SalesOrder::FULFILLMENT_PENDING,
                'shipping_method' => $validatedData['shipping_method'] ?? null,
                'shipping_cost' => $validatedData['shipping_cost'] ?? 0,
                'shipping_address' => $validatedData['shipping_address'] ?? null,
                'shipping_city' => $validatedData['shipping_city'] ?? null,
                'shipping_state' => $validatedData['shipping_state'] ?? null,
                'shipping_country' => $validatedData['shipping_country'] ?? null,
                'shipping_postal_code' => $validatedData['shipping_postal_code'] ?? null,
                'billing_address' => $validatedData['billing_address'] ?? null,
                'billing_city' => $validatedData['billing_city'] ?? null,
                'billing_state' => $validatedData['billing_state'] ?? null,
                'billing_country' => $validatedData['billing_country'] ?? null,
                'billing_postal_code' => $validatedData['billing_postal_code'] ?? null,
                'total_amount' => $totalAmount,
                'total_quantity' => $totalQuantity,
                'notes' => $validatedData['notes'] ?? null,
                'created_by' => Auth::id(),
                'source' => 'web',
            ]);
            
            // Create sales order items
            foreach ($validatedData['items'] as $item) {
                $totalPrice = $item['quantity'] * $item['unit_price'];
                
                $orderItem = SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $totalPrice,
                    'unit_of_measure' => $item['unit_of_measure'] ?? null,
                    'description' => $item['description'] ?? null,
                    'status' => SalesOrderItem::STATUS_PENDING,
                ]);
                
                // If inventory items are specified, allocate them
                if (isset($item['inventory_id']) && $item['inventory_id']) {
                    $inventory = Inventory::findOrFail($item['inventory_id']);
                    $batchId = $item['inventory_batch_id'] ?? null;
                    
                    // Create inventory allocation
                    InventoryAllocation::create([
                        'sales_order_item_id' => $orderItem->id,
                        'item_id' => $item['item_id'],
                        'batch_id' => $batchId,
                        'warehouse_id' => $inventory->warehouse_id,
                        'bin_location_id' => $inventory->bin_location_id,
                        'quantity_allocated' => $item['quantity'],
                        'allocated_by' => Auth::id(),
                        'status' => 'allocated',
                    ]);
                    
                    // Update sales order item status
                    $orderItem->update([
                        'status' => SalesOrderItem::STATUS_ALLOCATED,
                        'quantity_allocated' => $item['quantity'],
                    ]);
                    
                    // If batch is specified, update its quantity
                    if ($batchId) {
                        $batch = InventoryBatch::findOrFail($batchId);
                        if ($batch->quantity < $item['quantity']) {
                            throw new \Exception('Not enough quantity available in the selected batch.');
                        }
                        
                        $batch->update([
                            'quantity' => $batch->quantity - $item['quantity']
                        ]);
                    }
                }
            }
            
            // Update sales order status if all items are allocated
            $allAllocated = $salesOrder->items()->where('status', '!=', SalesOrderItem::STATUS_ALLOCATED)->count() === 0;
            if ($allAllocated) {
                $salesOrder->update([
                    'status' => SalesOrder::STATUS_PROCESSING,
                    'fulfillment_status' => SalesOrder::FULFILLMENT_PENDING
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('sales-orders.index')
                ->with('success', 'Sales order created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create sales order: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to create sales order: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Display the specified sales order.
     */
    public function show(Request $request, $id)
    {
        $business = $request->session()->get('business_id');
        
        $salesOrder = SalesOrder::with([
                'customer',
                'items' => function ($query) {
                    $query->with([
                        'item',
                        'allocations' => function ($query) {
                            $query->with(['warehouse', 'binLocation', 'batch']);
                        }
                    ]);
                },
                'creator',
                'shipments' => function ($query) {
                    $query->with(['shippedBy', 'items']);
                }
            ])
            ->where('business_id', $business)
            ->findOrFail($id);
            
        return Inertia::render('Inventory/Sales/Show', [
            'salesOrder' => $salesOrder,
            'statusText' => SalesOrder::$statusText,
            'statusClass' => SalesOrder::$statusClass,
            'paymentStatusText' => SalesOrder::$paymentStatusText,
            'paymentStatusClass' => SalesOrder::$paymentStatusClass,
            'fulfillmentStatusText' => SalesOrder::$fulfillmentStatusText,
            'fulfillmentStatusClass' => SalesOrder::$fulfillmentStatusClass,
            'canEdit' => $salesOrder->status === SalesOrder::STATUS_DRAFT,
            'canConfirm' => $salesOrder->status === SalesOrder::STATUS_DRAFT,
            'canShip' => in_array($salesOrder->status, [
                SalesOrder::STATUS_CONFIRMED,
                SalesOrder::STATUS_PROCESSING,
                SalesOrder::STATUS_PICKING,
                SalesOrder::STATUS_PACKING
            ])
        ]);
    }

    /**
     * Show the form for editing the specified sales order.
     */
    public function edit(Request $request, $id)
    {
        $business = $request->session()->get('business_id');
        
        $salesOrder = SalesOrder::with([
                'items' => function ($query) {
                    $query->with([
                        'item',
                        'allocations' => function ($query) {
                            $query->with(['warehouse', 'binLocation', 'batch']);
                        }
                    ]);
                },
                'customer'
            ])
            ->where('business_id', $business)
            ->findOrFail($id);
            
        // Only draft sales orders can be edited
        if ($salesOrder->status !== SalesOrder::STATUS_DRAFT) {
            return redirect()->route('sales-orders.show', $id)
                ->with('error', 'Only draft sales orders can be edited.');
        }
        
        $customers = Customer::where('business_id', $business)
            ->select('id', 'full_names', 'email', 'phone', 'address', 'city', 'state', 'country', 'postal_code')
            ->get();
            
        $items = ResourceItem::whereHas('inventories', function ($query) use ($business) {
                $query->where('business_id', $business)
                      ->where('quantity', '>', 0);
            })
            ->where('business_id', $business)
            ->select('id', 'item_name', 'price', 'unit')
            ->with(['inventories' => function ($query) {
                $query->where('quantity', '>', 0)
                      ->with(['warehouse:id,name', 'binLocation:id,name', 'batches' => function ($query) {
                          $query->where('quantity', '>', 0)
                                ->where('status', 'available');
                      }]);
            }])
            ->get();

        $warehouses = Warehouse::where('business_id', $business)
            ->select('id', 'name')
            ->with('binLocations:id,warehouse_id,name')
            ->get();
            
        return Inertia::render('Inventory/Sales/Edit', [
            'salesOrder' => $salesOrder,
            'customers' => $customers,
            'items' => $items,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the specified sales order in storage.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'required_date' => 'nullable|date|after_or_equal:order_date',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_cost' => 'nullable|numeric|min:0',
            'shipping_address' => 'nullable|string|max:255',
            'shipping_city' => 'nullable|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_country' => 'nullable|string|max:255',
            'shipping_postal_code' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'billing_city' => 'nullable|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_country' => 'nullable|string|max:255',
            'billing_postal_code' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:sales_order_items,id',
            'items.*.item_id' => 'required|exists:resource_item,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.inventory_id' => 'nullable|exists:inventories,id',
            'items.*.inventory_batch_id' => 'nullable|exists:inventory_batches,id',
            'items.*.unit_of_measure' => 'nullable|string|max:255',
            'items.*.description' => 'nullable|string',
        ]);

        $business = $request->session()->get('business_id');
        
        $salesOrder = SalesOrder::where('business_id', $business)
            ->findOrFail($id);
            
        // Only draft sales orders can be updated
        if ($salesOrder->status !== SalesOrder::STATUS_DRAFT) {
            return redirect()->route('sales-orders.show', $id)
                ->with('error', 'Only draft sales orders can be updated.');
        }
        
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
            
            // Update sales order
            $salesOrder->update([
                'customer_id' => $validatedData['customer_id'],
                'order_date' => $validatedData['order_date'],
                'required_date' => $validatedData['required_date'] ?? null,
                'shipping_method' => $validatedData['shipping_method'] ?? null,
                'shipping_cost' => $validatedData['shipping_cost'] ?? 0,
                'shipping_address' => $validatedData['shipping_address'] ?? null,
                'shipping_city' => $validatedData['shipping_city'] ?? null,
                'shipping_state' => $validatedData['shipping_state'] ?? null,
                'shipping_country' => $validatedData['shipping_country'] ?? null,
                'shipping_postal_code' => $validatedData['shipping_postal_code'] ?? null,
                'billing_address' => $validatedData['billing_address'] ?? null,
                'billing_city' => $validatedData['billing_city'] ?? null,
                'billing_state' => $validatedData['billing_state'] ?? null,
                'billing_country' => $validatedData['billing_country'] ?? null,
                'billing_postal_code' => $validatedData['billing_postal_code'] ?? null,
                'total_amount' => $totalAmount,
                'total_quantity' => $totalQuantity,
                'notes' => $validatedData['notes'] ?? null,
            ]);
            
            // Get existing items to track deleted ones
            $existingItemIds = $salesOrder->items->pluck('id')->toArray();
            $updatedItemIds = [];
            
            foreach ($validatedData['items'] as $itemData) {
                $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
                
                if (isset($itemData['id']) && $itemData['id']) {
                    // Update existing item
                    $orderItem = SalesOrderItem::find($itemData['id']);
                    
                    // If item exists and belongs to this order
                    if ($orderItem && $orderItem->sales_order_id == $salesOrder->id) {
                        $updatedItemIds[] = $orderItem->id;
                        
                        // First remove any existing allocations
                        if ($orderItem->allocations->count() > 0) {
                            // Restore batch quantities
                            foreach ($orderItem->allocations as $allocation) {
                                if ($allocation->batch_id) {
                                    $batch = InventoryBatch::findOrFail($allocation->batch_id);
                                    $batch->update([
                                        'quantity' => $batch->quantity + $allocation->quantity_allocated
                                    ]);
                                }
                            }
                            // Delete allocations
                            $orderItem->allocations()->delete();
                        }
                        
                        // Update the item
                        $orderItem->update([
                            'item_id' => $itemData['item_id'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'total_price' => $totalPrice,
                            'unit_of_measure' => $itemData['unit_of_measure'] ?? null,
                            'description' => $itemData['description'] ?? null,
                            'status' => SalesOrderItem::STATUS_PENDING,
                            'quantity_allocated' => 0,
                        ]);
                    }
                } else {
                    // Create new item
                    $orderItem = SalesOrderItem::create([
                        'sales_order_id' => $salesOrder->id,
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $totalPrice,
                        'unit_of_measure' => $itemData['unit_of_measure'] ?? null,
                        'description' => $itemData['description'] ?? null,
                        'status' => SalesOrderItem::STATUS_PENDING,
                    ]);
                    
                    $updatedItemIds[] = $orderItem->id;
                }
                
                // If inventory items are specified, allocate them
                if (isset($itemData['inventory_id']) && $itemData['inventory_id']) {
                    $inventory = Inventory::findOrFail($itemData['inventory_id']);
                    $batchId = $itemData['inventory_batch_id'] ?? null;
                    
                    // Create inventory allocation
                    InventoryAllocation::create([
                        'sales_order_item_id' => $orderItem->id,
                        'item_id' => $itemData['item_id'],
                        'batch_id' => $batchId,
                        'warehouse_id' => $inventory->warehouse_id,
                        'bin_location_id' => $inventory->bin_location_id,
                        'quantity_allocated' => $itemData['quantity'],
                        'allocated_by' => Auth::id(),
                        'status' => 'allocated',
                    ]);
                    
                    // Update sales order item status
                    $orderItem->update([
                        'status' => SalesOrderItem::STATUS_ALLOCATED,
                        'quantity_allocated' => $itemData['quantity'],
                    ]);
                    
                    // If batch is specified, update its quantity
                    if ($batchId) {
                        $batch = InventoryBatch::findOrFail($batchId);
                        if ($batch->quantity < $itemData['quantity']) {
                            throw new \Exception('Not enough quantity available in the selected batch.');
                        }
                        
                        $batch->update([
                            'quantity' => $batch->quantity - $itemData['quantity']
                        ]);
                    }
                }
            }
            
            // Delete removed items
            $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
            foreach ($itemsToDelete as $itemId) {
                $item = SalesOrderItem::find($itemId);
                if ($item) {
                    // Restore batch quantities for deleted items
                    foreach ($item->allocations as $allocation) {
                        if ($allocation->batch_id) {
                            $batch = InventoryBatch::findOrFail($allocation->batch_id);
                            $batch->update([
                                'quantity' => $batch->quantity + $allocation->quantity_allocated
                            ]);
                        }
                    }
                    // Delete allocations and item
                    $item->allocations()->delete();
                    $item->delete();
                }
            }
            
            // Update sales order status if all items are allocated
            $allAllocated = $salesOrder->items()->where('status', '!=', SalesOrderItem::STATUS_ALLOCATED)->count() === 0;
            if ($allAllocated && $salesOrder->items()->count() > 0) {
                $salesOrder->update([
                    'status' => SalesOrder::STATUS_PROCESSING,
                    'fulfillment_status' => SalesOrder::FULFILLMENT_PENDING
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('sales-orders.show', $id)
                ->with('success', 'Sales order updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update sales order: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to update sales order: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Update the status of the specified sales order.
     */
    public function updateStatus(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required|integer|min:0|max:8',
            'payment_status' => 'nullable|integer|min:0|max:3',
            'notes' => 'nullable|string',
        ]);

        $business = $request->session()->get('business_id');
        
        $salesOrder = SalesOrder::where('business_id', $business)
            ->findOrFail($id);
            
        $oldStatus = $salesOrder->status;
        $newStatus = $validatedData['status'];
        
        // Validate status transitions
        $allowedTransitions = [
            SalesOrder::STATUS_DRAFT => [
                SalesOrder::STATUS_CONFIRMED,
                SalesOrder::STATUS_CANCELLED
            ],
            SalesOrder::STATUS_CONFIRMED => [
                SalesOrder::STATUS_PROCESSING,
                SalesOrder::STATUS_CANCELLED,
                SalesOrder::STATUS_ON_HOLD
            ],
            SalesOrder::STATUS_PROCESSING => [
                SalesOrder::STATUS_PICKING,
                SalesOrder::STATUS_CANCELLED,
                SalesOrder::STATUS_ON_HOLD
            ],
            SalesOrder::STATUS_PICKING => [
                SalesOrder::STATUS_PACKING,
                SalesOrder::STATUS_CANCELLED,
                SalesOrder::STATUS_ON_HOLD
            ],
            SalesOrder::STATUS_PACKING => [
                SalesOrder::STATUS_SHIPPED,
                SalesOrder::STATUS_CANCELLED,
                SalesOrder::STATUS_ON_HOLD
            ],
            SalesOrder::STATUS_SHIPPED => [
                SalesOrder::STATUS_DELIVERED,
                SalesOrder::STATUS_ON_HOLD
            ],
            SalesOrder::STATUS_ON_HOLD => [
                $oldStatus, // Can revert to previous status
                SalesOrder::STATUS_CANCELLED
            ]
        ];
        
        if (!isset($allowedTransitions[$oldStatus]) || !in_array($newStatus, $allowedTransitions[$oldStatus])) {
            return back()->withErrors([
                'error' => 'Invalid status transition.'
            ]);
        }
        
        try {
            DB::beginTransaction();
            
            $updateData = [
                'status' => $newStatus
            ];
            
            // Update payment status if provided
            if (isset($validatedData['payment_status'])) {
                $updateData['payment_status'] = $validatedData['payment_status'];
            }
            
            // Handle special status transitions
            if ($newStatus === SalesOrder::STATUS_CONFIRMED) {
                $updateData['status'] = SalesOrder::STATUS_CONFIRMED;
            } else if ($newStatus === SalesOrder::STATUS_DELIVERED) {
                $updateData['fulfillment_status'] = SalesOrder::FULFILLMENT_FULFILLED;
            } else if ($newStatus === SalesOrder::STATUS_CANCELLED) {
                // Return allocated inventory
                foreach ($salesOrder->items as $item) {
                    foreach ($item->allocations as $allocation) {
                        if ($allocation->batch_id) {
                            $batch = InventoryBatch::findOrFail($allocation->batch_id);
                            $batch->update([
                                'quantity' => $batch->quantity + $allocation->quantity_allocated
                            ]);
                        }
                    }
                }
            }
            
            if ($validatedData['notes'] ?? false) {
                $notes = $salesOrder->notes ?? '';
                $notes .= "\n" . now()->format('Y-m-d H:i') . " - Status changed from " . 
                    SalesOrder::$statusText[$oldStatus] . " to " . 
                    SalesOrder::$statusText[$newStatus] . ": " . $validatedData['notes'];
                $updateData['notes'] = trim($notes);
            }
            
            $salesOrder->update($updateData);
            
            DB::commit();
            
            return redirect()->route('sales-orders.show', $id)
                ->with('success', 'Sales order status updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update sales order status: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to update sales order status. Please try again.'
            ]);
        }
    }

    /**
     * Remove the specified sales order from storage.
     */
    public function destroy(Request $request, $id)
    {
        $business = $request->session()->get('business_id');
        
        $salesOrder = SalesOrder::where('business_id', $business)
            ->findOrFail($id);
            
        // Only draft sales orders can be deleted
        if ($salesOrder->status !== SalesOrder::STATUS_DRAFT) {
            return back()->withErrors([
                'error' => 'Only draft sales orders can be deleted.'
            ]);
        }
        
        try {
            DB::beginTransaction();
            
            // Return allocated inventory
            foreach ($salesOrder->items as $item) {
                foreach ($item->allocations as $allocation) {
                    if ($allocation->batch_id) {
                        $batch = InventoryBatch::findOrFail($allocation->batch_id);
                        $batch->update([
                            'quantity' => $batch->quantity + $allocation->quantity_allocated
                        ]);
                    }
                }
                // Delete allocations
                $item->allocations()->delete();
            }
            
            // Delete sales order items
            $salesOrder->items()->delete();
            
            // Delete sales order
            $salesOrder->delete();
            
            DB::commit();
            
            return redirect()->route('sales-orders.index')
                ->with('success', 'Sales order deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete sales order: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to delete sales order. Please try again.'
            ]);
        }
    }

    /**
     * Generate the next sequential order number
     */
    protected function generateNextOrderNumber($businessId)
    {
        $business = Business::find($businessId);
        $businessCode = strtoupper(substr($business->business_name, 0, 3));
        $latestOrder = SalesOrder::where('business_id', $businessId)
            ->where('order_number', 'like', "SO-{$businessCode}-%")
            ->orderBy('order_number', 'desc')
            ->first();
            
        if (!$latestOrder) {
            return "SO-{$businessCode}-001";
        }
        
        preg_match('/SO-[A-Z]+-(\d+)/', $latestOrder->order_number, $matches);
        if (!isset($matches[1])) {
            return "SO-{$businessCode}-001";
        }
        
        $nextNumber = str_pad(intval($matches[1]) + 1, 3, '0', STR_PAD_LEFT);
        return "SO-{$businessCode}-{$nextNumber}";
    }
}
