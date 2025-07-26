<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShipmentController extends Controller
{
    public function salesIndex()
    {
        return Inertia::render('Shipments/Main');
    }

    public function index(Request $request)
    {
        $query = Shipment::with(['salesOrder', 'carrier', 'createdBy'])
            ->where('business_id', $request->business_id);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('salesOrder', function ($q2) use ($search) {
                      $q2->where('order_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('shipment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('shipment_date', '<=', $request->date_to);
        }

        $shipments = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('rows', 20));

        return response()->json($shipments);
    }

    public function show($id)
    {
        $shipment = Shipment::with([
                'salesOrder', 
                'carrier', 
                'shipmentItems.salesOrderItem', 
                'shipmentItems.inventoryBatch',
                'createdBy'
            ])
            ->findOrFail($id);

        return response()->json($shipment);
    }

    public function store(Request $request)
    {
        $request->validate([
            'business_id' => 'required|string|exists:business,business_id',
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'carrier_id' => 'nullable|exists:carriers,id',
            'shipment_date' => 'required|date',
            'tracking_number' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.inventory_batch_id' => 'nullable|exists:inventory_batches,id',
            'items.*.bin_location_id' => 'required|exists:bin_locations,id',
        ]);

        try {
            DB::beginTransaction();

            // Generate shipment number
            $shipmentNumber = 'SHP-' . strtoupper(Str::random(8));
            
            // Create shipment
            $shipment = Shipment::create([
                'business_id' => $request->business_id,
                'sales_order_id' => $request->sales_order_id,
                'warehouse_id' => $request->warehouse_id,
                'carrier_id' => $request->carrier_id,
                'shipment_number' => $shipmentNumber,
                'shipment_date' => $request->shipment_date,
                'tracking_number' => $request->tracking_number,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'status' => Shipment::STATUS_PENDING,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Create shipment items
            foreach ($request->items as $item) {
                $salesOrderItem = SalesOrderItem::findOrFail($item['sales_order_item_id']);
                
                $shipmentItem = ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'sales_order_item_id' => $item['sales_order_item_id'],
                    'inventory_batch_id' => $item['inventory_batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'status' => ShipmentItem::STATUS_PENDING,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Create stock movement record
                StockMovement::create([
                    'business_id' => $request->business_id,
                    'item_id' => $salesOrderItem->item_id,
                    'warehouse_id' => $request->warehouse_id,
                    'bin_location_id' => $item['bin_location_id'],
                    'batch_id' => $item['inventory_batch_id'] ?? null,
                    'quantity' => -$item['quantity'], // Negative for outbound
                    'movement_type' => 'outbound',
                    'reference_type' => 'shipment_item',
                    'reference_id' => $shipmentItem->id,
                    'notes' => 'Shipment allocation',
                    'created_by' => Auth::id(),
                ]);

                // Update sales order item status if needed
                $totalShipped = ShipmentItem::where('sales_order_item_id', $salesOrderItem->id)
                    ->sum('quantity');
                
                if ($totalShipped >= $salesOrderItem->quantity) {
                    $salesOrderItem->status = SalesOrderItem::STATUS_SHIPPED;
                } elseif ($totalShipped > 0) {
                    $salesOrderItem->status = SalesOrderItem::STATUS_PARTIAL;
                }
                $salesOrderItem->save();
            }

            // Update sales order status
            $salesOrder = SalesOrder::findOrFail($request->sales_order_id);
            $allItemsShipped = SalesOrderItem::where('sales_order_id', $salesOrder->id)
                ->where('status', '!=', SalesOrderItem::STATUS_SHIPPED)
                ->count() === 0;
            
            $anyItemsShipped = SalesOrderItem::where('sales_order_id', $salesOrder->id)
                ->where('status', SalesOrderItem::STATUS_SHIPPED)
                ->orWhere('status', SalesOrderItem::STATUS_PARTIAL)
                ->count() > 0;
            
            if ($allItemsShipped) {
                $salesOrder->fulfillment_status = SalesOrder::FULFILLMENT_STATUS_FULFILLED;
            } elseif ($anyItemsShipped) {
                $salesOrder->fulfillment_status = SalesOrder::FULFILLMENT_STATUS_PARTIAL;
            }
            $salesOrder->save();

            DB::commit();

            return response()->json([
                'message' => 'Shipment created successfully',
                'shipment' => $shipment
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating shipment: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'carrier_id' => 'nullable|exists:carriers,id',
            'tracking_number' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'status' => 'sometimes|integer',
            'shipment_date' => 'sometimes|date',
        ]);

        try {
            DB::beginTransaction();

            $shipment = Shipment::findOrFail($id);
            
            // Update shipment details
            if ($request->has('carrier_id')) {
                $shipment->carrier_id = $request->carrier_id;
            }
            
            if ($request->has('tracking_number')) {
                $shipment->tracking_number = $request->tracking_number;
            }
            
            if ($request->has('shipping_cost')) {
                $shipment->shipping_cost = $request->shipping_cost;
            }
            
            if ($request->has('shipment_date')) {
                $shipment->shipment_date = $request->shipment_date;
            }
            
            if ($request->has('notes')) {
                $shipment->notes = $request->notes;
            }
            
            // Update status if provided
            if ($request->has('status')) {
                $oldStatus = $shipment->status;
                $shipment->status = $request->status;
                
                // If status changed to shipped, update shipment items and sales order
                if ($oldStatus != Shipment::STATUS_SHIPPED && $request->status == Shipment::STATUS_SHIPPED) {
                    // Update all shipment items to shipped
                    ShipmentItem::where('shipment_id', $shipment->id)
                        ->update(['status' => ShipmentItem::STATUS_SHIPPED]);
                    
                    // Update sales order fulfillment status
                    $salesOrder = SalesOrder::findOrFail($shipment->sales_order_id);
                    $allItemsShipped = SalesOrderItem::where('sales_order_id', $salesOrder->id)
                        ->where('status', '!=', SalesOrderItem::STATUS_SHIPPED)
                        ->count() === 0;
                    
                    if ($allItemsShipped) {
                        $salesOrder->fulfillment_status = SalesOrder::FULFILLMENT_STATUS_FULFILLED;
                    } else {
                        $salesOrder->fulfillment_status = SalesOrder::FULFILLMENT_STATUS_PARTIAL;
                    }
                    $salesOrder->save();
                }
                
                // If status changed to delivered, update shipment items and sales order
                if ($request->status == Shipment::STATUS_DELIVERED) {
                    // Update all shipment items to delivered
                    ShipmentItem::where('shipment_id', $shipment->id)
                        ->update(['status' => ShipmentItem::STATUS_DELIVERED]);
                }
            }
            
            $shipment->save();

            DB::commit();

            return response()->json([
                'message' => 'Shipment updated successfully',
                'shipment' => $shipment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating shipment: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            
            $shipment = Shipment::findOrFail($id);
            
            // Only allow deletion if shipment is in pending status
            if ($shipment->status != Shipment::STATUS_PENDING) {
                return response()->json([
                    'message' => 'Cannot delete shipment that is not in pending status'
                ], 422);
            }
            
            // Revert stock movements
            $shipmentItems = ShipmentItem::where('shipment_id', $id)->get();
            foreach ($shipmentItems as $item) {
                // Find and delete stock movements
                StockMovement::where('reference_type', 'shipment_item')
                    ->where('reference_id', $item->id)
                    ->delete();
                
                // Update sales order item status
                $salesOrderItem = SalesOrderItem::find($item->sales_order_item_id);
                if ($salesOrderItem) {
                    $totalShipped = ShipmentItem::where('sales_order_item_id', $salesOrderItem->id)
                        ->where('shipment_id', '!=', $id)
                        ->sum('quantity');
                    
                    if ($totalShipped > 0) {
                        $salesOrderItem->status = SalesOrderItem::STATUS_PARTIAL;
                    } else {
                        $salesOrderItem->status = SalesOrderItem::STATUS_PENDING;
                    }
                    $salesOrderItem->save();
                }
                
                // Delete the shipment item
                $item->delete();
            }
            
            // Update sales order fulfillment status
            $salesOrder = SalesOrder::findOrFail($shipment->sales_order_id);
            $anyItemsShipped = SalesOrderItem::where('sales_order_id', $salesOrder->id)
                ->where(function($query) {
                    $query->where('status', SalesOrderItem::STATUS_SHIPPED)
                          ->orWhere('status', SalesOrderItem::STATUS_PARTIAL);
                })
                ->count() > 0;
            
            if (!$anyItemsShipped) {
                $salesOrder->fulfillment_status = SalesOrder::FULFILLMENT_STATUS_UNFULFILLED;
            } else {
                $salesOrder->fulfillment_status = SalesOrder::FULFILLMENT_STATUS_PARTIAL;
            }
            $salesOrder->save();
            
            // Delete the shipment
            $shipment->delete();
            
            DB::commit();
            
            return response()->json(['message' => 'Shipment deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting shipment: ' . $e->getMessage()], 500);
        }
    }

    public function markAsShipped($id)
    {
        try {
            DB::beginTransaction();
            
            $shipment = Shipment::findOrFail($id);
            $shipment->status = Shipment::STATUS_SHIPPED;
            $shipment->save();
            
            // Update all shipment items to shipped
            ShipmentItem::where('shipment_id', $shipment->id)
                ->update(['status' => ShipmentItem::STATUS_SHIPPED]);
            
            // Update sales order fulfillment status
            $salesOrder = SalesOrder::findOrFail($shipment->sales_order_id);
            $allItemsShipped = SalesOrderItem::where('sales_order_id', $salesOrder->id)
                ->where('status', '!=', SalesOrderItem::STATUS_SHIPPED)
                ->count() === 0;
            
            if ($allItemsShipped) {
                $salesOrder->fulfillment_status = SalesOrder::FULFILLMENT_STATUS_FULFILLED;
            } else {
                $salesOrder->fulfillment_status = SalesOrder::FULFILLMENT_STATUS_PARTIAL;
            }
            $salesOrder->save();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Shipment marked as shipped',
                'shipment' => $shipment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating shipment: ' . $e->getMessage()], 500);
        }
    }

    public function markAsDelivered($id)
    {
        try {
            DB::beginTransaction();
            
            $shipment = Shipment::findOrFail($id);
            $shipment->status = Shipment::STATUS_DELIVERED;
            $shipment->save();
            
            // Update all shipment items to delivered
            ShipmentItem::where('shipment_id', $shipment->id)
                ->update(['status' => ShipmentItem::STATUS_DELIVERED]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Shipment marked as delivered',
                'shipment' => $shipment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error updating shipment: ' . $e->getMessage()], 500);
        }
    }
}
