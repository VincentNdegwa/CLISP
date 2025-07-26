<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\ResourceItem;
use App\Models\Warehouse;
use App\Models\BinLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InventoryManager
{
    /**
     * Adjust inventory quantity with full tracking and history
     *
     * @param array $data - Contains all necessary information for adjustment
     * @return array - Contains status, message, inventory, and any errors
     */
    public function adjustQuantity(array $data)
    {
        // Data validation should happen before this point in the controller
        try {
            DB::beginTransaction();

            // Required fields
            $inventoryId = $data['inventory_id'];
            $adjustmentType = $data['adjustment_type']; // 'increase' or 'decrease'
            $quantity = $data['quantity'];
            $referenceType = $data['reference_type']; // 'stock_adjustment', 'sale', 'purchase', 'return', etc.
            $referenceId = $data['reference_id'] ?? null;
            $reasonId = $data['reason_id'] ?? null;

            // Optional fields with defaults
            $notes = $data['notes'] ?? null;
            $batchId = $data['batch_id'] ?? null;
            $userId = $data['user_id'] ?? Auth::id();

            // Find the inventory record
            $inventory = Inventory::findOrFail($inventoryId);
            $item = $inventory->item;
            $currentQuantity = $inventory->quantity;

            // Calculate new quantity
            if ($adjustmentType === 'increase') {
                $newQuantity = $currentQuantity + $quantity;
            } else {
                // Check if we have enough quantity to decrease
                Log::info("Current quantity: $currentQuantity, Quantity to subtract: $quantity");
                if ($quantity > $currentQuantity) {
                    DB::rollBack();
                    return [
                        'status' => false,
                        'message' => 'Cannot subtract more than the current inventory quantity',
                        'errors' => ['quantity' => ['Not enough quantity available']]
                    ];
                }
                $newQuantity = $currentQuantity - $quantity;
            }

            // Update the batch if specified
            if ($batchId) {
                $batch = InventoryBatch::findOrFail($batchId);
                $batchCurrentQty = $batch->quantity;

                if ($adjustmentType === 'increase') {
                    $batch->quantity += $quantity;
                } else {
                    if ($quantity > $batchCurrentQty) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => 'Cannot subtract more than the current batch quantity',
                            'errors' => ['quantity' => ['Not enough quantity available in this batch']]
                        ];
                    }
                    $batch->quantity -= $quantity;
                }

                $batch->save();
            }

            // Update inventory quantity
            $inventory->quantity = $newQuantity;

            // Update inventory status based on new quantity
            $this->updateInventoryStatus($inventory, $newQuantity);

            // Save inventory changes
            $inventory->save();

            // Create stock adjustment record
            $adjustment = StockAdjustment::create([
                'inventory_id' => $inventory->id,
                'item_id' => $inventory->item_id,
                'business_id' => $inventory->business_id,
                'user_id' => $userId,
                'adjustment_type' => $adjustmentType,
                'quantity' => $quantity,
                'previous_quantity' => $currentQuantity,
                'new_quantity' => $newQuantity,
                'reason_id' => $reasonId,
                'notes' => $notes,
                'date' => now(),
                'reference' => $referenceType . ':' . $referenceId,
                'batch_id' => $batchId,
            ]);

            // Create stock movement for tracking
            $movement = StockMovement::create([
                'business_id' => $inventory->business_id,
                'item_id' => $inventory->item_id,
                'batch_id' => $batchId,
                'from_warehouse_id' => $adjustmentType === 'decrease' ? $inventory->warehouse_id : null,
                'from_bin_location_id' => $adjustmentType === 'decrease' ? $inventory->bin_location_id : null,
                'to_warehouse_id' => $adjustmentType === 'increase' ? $inventory->warehouse_id : null,
                'to_bin_location_id' => $adjustmentType === 'increase' ? $inventory->bin_location_id : null,
                'quantity' => $adjustmentType === 'increase' ? $quantity : -$quantity,
                'movement_type' => $this->determineMovementType($referenceType),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes ?? $this->generateDefaultNote($referenceType, $adjustmentType),
                'approved_by' => $userId,
                'approved_date' => now(),
            ]);

            // Load relationships for return value
            $inventory->load(['item', 'warehouse', 'binLocation']);

            DB::commit();

            // Return success
            return [
                'status' => true,
                'message' => 'Inventory quantity adjusted successfully',
                'inventory' => $inventory,
                'adjustment' => $adjustment,
                'movement' => $movement
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory adjustment failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => false,
                'message' => 'Failed to adjust inventory quantity',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Transfer stock between locations
     *
     * @param array $data - Contains transfer details
     * @return array - Contains status, message, and details
     */
    public function transferStock(array $data)
    {
        try {
            DB::beginTransaction();

            $sourceInventoryId = $data['source_inventory_id'];
            $destinationInventoryId = $data['destination_inventory_id'] ?? null;
            $quantity = $data['quantity'];
            $referenceType = $data['reference_type'] ?? 'manual_transfer';
            $referenceId = $data['reference_id'] ?? null;
            $notes = $data['notes'] ?? 'Stock transfer';
            $userId = $data['user_id'] ?? Auth::id();
            $batchId = $data['batch_id'] ?? null;

            // Get source inventory
            $sourceInventory = Inventory::findOrFail($sourceInventoryId);

            // Check if we have enough stock to transfer
            if ($sourceInventory->quantity < $quantity) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'Insufficient stock for transfer',
                    'errors' => ['quantity' => ['Not enough quantity available to transfer']]
                ];
            }

            // Decrease source inventory
            $sourceResult = $this->adjustQuantity([
                'inventory_id' => $sourceInventoryId,
                'adjustment_type' => 'decrease',
                'quantity' => $quantity,
                'reference_type' => 'transfer_out',
                'reference_id' => $referenceId,
                'notes' => $notes,
                'user_id' => $userId,
                'batch_id' => $batchId,
            ]);

            if (!$sourceResult['status']) {
                DB::rollBack();
                return $sourceResult;
            }

            // Handle destination - either existing inventory or create new
            if ($destinationInventoryId) {
                // Increase existing destination inventory
                $destinationResult = $this->adjustQuantity([
                    'inventory_id' => $destinationInventoryId,
                    'adjustment_type' => 'increase',
                    'quantity' => $quantity,
                    'reference_type' => 'transfer_in',
                    'reference_id' => $referenceId,
                    'notes' => $notes,
                    'user_id' => $userId,
                    'batch_id' => $batchId,
                ]);

                if (!$destinationResult['status']) {
                    DB::rollBack();
                    return $destinationResult;
                }

                $destinationInventory = $destinationResult['inventory'];
            } else {
                // Need to create new inventory in destination location
                $destinationWarehouseId = $data['destination_warehouse_id'];
                $destinationBinLocationId = $data['destination_bin_location_id'] ?? null;

                // Create new inventory in destination
                $destinationInventory = Inventory::create([
                    'item_id' => $sourceInventory->item_id,
                    'warehouse_id' => $destinationWarehouseId,
                    'bin_location_id' => $destinationBinLocationId,
                    'quantity' => $quantity,
                    'business_id' => $sourceInventory->business_id,
                    'status' => $quantity > 0 ? Inventory::STATUS_IN_STOCK : Inventory::STATUS_OUT_OF_STOCK,
                    'reorder_point' => $sourceInventory->reorder_point,
                    'reorder_quantity' => $sourceInventory->reorder_quantity,
                ]);

                // Create stock movement for new inventory
                StockMovement::create([
                    'business_id' => $sourceInventory->business_id,
                    'item_id' => $sourceInventory->item_id,
                    'batch_id' => $batchId,
                    'from_warehouse_id' => $sourceInventory->warehouse_id,
                    'from_bin_location_id' => $sourceInventory->bin_location_id,
                    'to_warehouse_id' => $destinationWarehouseId,
                    'to_bin_location_id' => $destinationBinLocationId,
                    'quantity' => $quantity,
                    'movement_type' => 'transfer',
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'notes' => $notes,
                    'approved_by' => $userId,
                    'approved_date' => now(),
                ]);
            }

            // Create the transfer record to link both sides
            $transfer = [
                'source_inventory' => $sourceInventory,
                'destination_inventory' => $destinationInventory,
                'quantity' => $quantity,
                'date' => now(),
                'user_id' => $userId,
            ];

            DB::commit();

            return [
                'status' => true,
                'message' => 'Stock transferred successfully',
                'transfer' => $transfer
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock transfer failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => false,
                'message' => 'Failed to transfer stock',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process inventory batch operations
     *
     * @param array $data - Contains batch details
     * @return array - Contains status and results
     */
    public function processBatchOperation(array $data)
    {
        try {
            DB::beginTransaction();

            $inventoryId = $data['inventory_id'];
            $operation = $data['operation']; // 'create', 'adjust', 'expire', 'damage'
            $quantity = $data['quantity'] ?? 0;
            $batchData = $data['batch_data'] ?? [];
            $userId = $data['user_id'] ?? Auth::id();
            $reasonId = $data['reason_id'] ?? null;

            $inventory = Inventory::findOrFail($inventoryId);
            $batch = null;

            switch ($operation) {
                case 'create':
                    // Create a new batch
                    $batch = InventoryBatch::create(array_merge($batchData, [
                        'inventory_id' => $inventoryId,
                        'quantity' => $quantity,
                        'status' => 'available'
                    ]));

                    // Increase inventory quantity
                    $adjustResult = $this->adjustQuantity([
                        'inventory_id' => $inventoryId,
                        'adjustment_type' => 'increase',
                        'quantity' => $quantity,
                        'reference_type' => 'batch_created',
                        'reference_id' => $batch->id,
                        'notes' => "Batch {$batch->batch_number} created",
                        'user_id' => $userId,
                        'batch_id' => $batch->id,
                        'reason_id' => $reasonId
                    ]);

                    if (!$adjustResult['status']) {
                        DB::rollBack();
                        return $adjustResult;
                    }

                    break;

                case 'adjust':
                    // Adjust quantity in existing batch
                    $batchId = $data['batch_id'];
                    $batch = InventoryBatch::findOrFail($batchId);
                    $oldQuantity = $batch->quantity;
                    $adjustmentType = $quantity > $oldQuantity ? 'increase' : 'decrease';
                    $changeAmount = abs($quantity - $oldQuantity);

                    if ($changeAmount > 0) {
                        $adjustResult = $this->adjustQuantity([
                            'inventory_id' => $inventoryId,
                            'adjustment_type' => $adjustmentType,
                            'quantity' => $changeAmount,
                            'reference_type' => 'batch_adjusted',
                            'reference_id' => $batch->id,
                            'notes' => "Batch {$batch->batch_number} quantity adjusted",
                            'user_id' => $userId,
                            'batch_id' => $batch->id,
                            'reason_id' => $reasonId

                        ]);

                        if (!$adjustResult['status']) {
                            DB::rollBack();
                            return $adjustResult;
                        }

                        // Update batch quantity
                        $batch->quantity = $quantity;
                        $batch->save();
                    }

                    break;

                case 'expire':
                    // Mark batch as expired
                    $batchId = $data['batch_id'];
                    $batch = InventoryBatch::findOrFail($batchId);
                    $batch->status = 'expired';
                    $batch->save();

                    // Update inventory if necessary
                    if ($data['adjust_inventory'] ?? true) {
                        $adjustResult = $this->adjustQuantity([
                            'inventory_id' => $inventoryId,
                            'adjustment_type' => 'decrease',
                            'quantity' => $batch->quantity,
                            'reference_type' => 'batch_expired',
                            'reference_id' => $batch->id,
                            'notes' => "Batch {$batch->batch_number} expired",
                            'user_id' => $userId,
                            'batch_id' => $batch->id,
                            'reason_id' => $reasonId

                        ]);

                        if (!$adjustResult['status']) {
                            DB::rollBack();
                            return $adjustResult;
                        }
                    }

                    break;

                case 'damage':
                    // Mark batch as damaged
                    $batchId = $data['batch_id'];
                    $damagedQuantity = $data['damaged_quantity'] ?? null;
                    $batch = InventoryBatch::findOrFail($batchId);

                    if ($damagedQuantity !== null && $damagedQuantity < $batch->quantity) {
                        // Partial damage - reduce quantity
                        $adjustResult = $this->adjustQuantity([
                            'inventory_id' => $inventoryId,
                            'adjustment_type' => 'decrease',
                            'quantity' => $damagedQuantity,
                            'reference_type' => 'batch_damaged',
                            'reference_id' => $batch->id,
                            'notes' => "Batch {$batch->batch_number} partially damaged",
                            'user_id' => $userId,
                            'batch_id' => $batch->id,
                            'reason_id' => $data['reason_id'] ?? null,
                        ]);

                        // Update batch quantity
                        $batch->quantity -= $damagedQuantity;
                        $batch->save();
                    } else {
                        // Full damage - mark batch as damaged
                        $batch->status = 'damaged';
                        $batch->save();

                        // Reduce inventory quantity
                        $adjustResult = $this->adjustQuantity([
                            'inventory_id' => $inventoryId,
                            'adjustment_type' => 'decrease',
                            'quantity' => $batch->quantity,
                            'reference_type' => 'batch_damaged',
                            'reference_id' => $batch->id,
                            'notes' => "Batch {$batch->batch_number} damaged",
                            'user_id' => $userId,
                            'batch_id' => $batch->id,
                            'reason_id' => $data['reason_id'] ?? null,
                        ]);
                    }

                    if (!$adjustResult['status']) {
                        DB::rollBack();
                        return $adjustResult;
                    }

                    break;
            }

            DB::commit();

            return [
                'status' => true,
                'message' => 'Batch operation completed successfully',
                'batch' => $batch,
                'inventory' => $inventory->fresh()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch operation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => false,
                'message' => 'Failed to process batch operation',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create or update inventory with proper tracking
     *
     * @param array $data - Contains inventory details
     * @return array - Contains status, message, and inventory details
     */
    public function createOrUpdateInventory(array $data)
    {
        try {
            DB::beginTransaction();

            $itemId = $data['item_id'];
            $warehouseId = $data['warehouse_id'];
            $binLocationId = $data['bin_location_id'] ?? null;
            $quantity = $data['quantity'] ?? 0;
            $businessId = $data['business_id'];
            $reorderPoint = $data['reorder_point'] ?? null;
            $minStockLevel = $data['min_stock_level'] ?? null;
            $maxStockLevel = $data['max_stock_level'] ?? null;
            $notes = $data['notes'] ?? 'Inventory update';
            $userId = $data['user_id'] ?? Auth::id();
            $batchId = $data['batch_id'] ?? null;

            // Check if inventory already exists
            $inventory = Inventory::where('business_id', $businessId)
                ->where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->where('bin_location_id', $binLocationId)
                ->first();

            $isNew = false;
            $oldQuantity = 0;

            if ($inventory) {
                // Update existing inventory
                $oldQuantity = $inventory->quantity;
                $inventory->quantity += $quantity;

                if (isset($data['reorder_point'])) {
                    $inventory->reorder_point = $reorderPoint;
                }

                if (isset($data['min_stock_level'])) {
                    $inventory->min_stock_level = $minStockLevel;
                }

                if (isset($data['max_stock_level'])) {
                    $inventory->max_stock_level = $maxStockLevel;
                }

                // Update inventory status based on new quantity
                $this->updateInventoryStatus($inventory, $inventory->quantity);

                $inventory->save();
            } else {
                // Create new inventory
                $isNew = true;
                $inventory = Inventory::create([
                    'item_id' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'bin_location_id' => $binLocationId,
                    'quantity' => $quantity,
                    'business_id' => $businessId,
                    'reorder_point' => $reorderPoint,
                    'min_stock_level' => $minStockLevel,
                    'max_stock_level' => $maxStockLevel,
                    'status' => $quantity > 0 ? Inventory::STATUS_IN_STOCK : Inventory::STATUS_OUT_OF_STOCK,
                ]);
            }

            // Create stock movement record
            $movement = StockMovement::create([
                'business_id' => $businessId,
                'item_id' => $itemId,
                'batch_id' => $batchId,
                'from_warehouse_id' => null,
                'from_bin_location_id' => null,
                'to_warehouse_id' => $warehouseId,
                'to_bin_location_id' => $binLocationId,
                'quantity' => $quantity,
                'movement_type' => $isNew ? 'initial' : 'adjustment',
                'reference_type' => $isNew ? 'inventory_creation' : 'inventory_update',
                'reference_id' => $inventory->id,
                'notes' => $notes,
                'approved_by' => $userId,
                'approved_date' => now(),
            ]);

            // Create stock adjustment record if not new inventory
            if (!$isNew && $quantity > 0) {
                $adjustment = StockAdjustment::create([
                    'inventory_id' => $inventory->id,
                    'item_id' => $itemId,
                    'business_id' => $BusinessId,
                    'user_id' => $userId,
                    'adjustment_type' => 'increase',
                    'quantity' => $quantity,
                    'previous_quantity' => $oldQuantity,
                    'new_quantity' => $inventory->quantity,
                    'reason_id' => $data['reason_id'] ?? null,
                    'notes' => $notes,
                    'date' => now(),
                    'reference' => 'inventory_update:' . $inventory->id,
                    'batch_id' => $batchId,
                ]);
            }

            DB::commit();

            return [
                'status' => true,
                'message' => $isNew ? 'Inventory created successfully' : 'Inventory updated successfully',
                'inventory' => $inventory->fresh(['item', 'warehouse', 'binLocation']),
                'is_new' => $isNew
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory creation/update failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => false,
                'message' => 'Failed to create/update inventory',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create inventory records from goods receipt
     * 
     * @param int $goodsReceiptId - The ID of the goods receipt
     * @param array $options - Additional options for inventory creation
     * @return array - Contains status, message, created inventory records, and any errors
     */
    public function createInventoryFromGoodsReceipt(int $goodsReceiptId, array $options = [])
    {
        try {
            DB::beginTransaction();

            // Find the goods receipt
            $goodsReceipt = GoodsReceipt::with(['items', 'warehouse', 'purchaseOrder'])->findOrFail($goodsReceiptId);
            
            if ($goodsReceipt->status === 'rejected') {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'Cannot create inventory for rejected goods receipt',
                    'errors' => ['goods_receipt' => ['Goods receipt has been rejected']]
                ];
            }
            
            $userId = $options['user_id'] ?? Auth::id();
            $warehouseId = $goodsReceipt->warehouse_id;
            $businessId = $goodsReceipt->business_id;
            $createdInventories = [];
            $createdBatches = [];
            
            foreach ($goodsReceipt->items as $receiptItem) {
                if ($receiptItem->status === 'rejected' || $receiptItem->quantity_received <= 0) {
                    continue; // Skip rejected or zero quantity items
                }
                
                // Find the item
                $item = ResourceItem::findOrFail($receiptItem->item_id);
                $binLocationId = $receiptItem->bin_location_id;
                $purchaseOrderItem = $receiptItem->purchaseOrderItem;
                $quantity = $receiptItem->quantity_received;
                
                // Check if inventory already exists for this item in the same warehouse and bin location
                $inventory = Inventory::where('item_id', $item->id)
                    ->where('warehouse_id', $warehouseId)
                    ->where('bin_location_id', $binLocationId)
                    ->first();
                
                // If inventory doesn't exist, create a new one
                if (!$inventory) {
                    $inventory = Inventory::create([
                        'item_id' => $item->id,
                        'warehouse_id' => $warehouseId,
                        'bin_location_id' => $binLocationId,
                        'quantity' => $quantity,
                        'business_id' => $businessId,
                        'status' => $quantity > 0 ? Inventory::STATUS_IN_STOCK : Inventory::STATUS_OUT_OF_STOCK,
                        'reorder_point' => $item->reorder_point ?? 0,
                        'reorder_quantity' => $item->reorder_quantity ?? 0,
                    ]);
                    
                    // Set initial status
                    $this->updateInventoryStatus($inventory, $quantity);
                    
                    // Create a stock movement for new inventory
                    StockMovement::create([
                        'business_id' => $businessId,
                        'item_id' => $item->id,
                        'to_warehouse_id' => $warehouseId,
                        'to_bin_location_id' => $binLocationId,
                        'quantity' => $quantity,
                        'movement_type' => 'receipt',
                        'reference_type' => 'goods_receipt',
                        'reference_id' => $goodsReceiptId,
                        'notes' => "Initial receipt of item {$item->name} from goods receipt #{$goodsReceipt->receipt_number}",
                        'approved_by' => $userId,
                        'approved_date' => now(),
                    ]);
                } else {
                    // If inventory exists, increase its quantity
                    $currentQuantity = $inventory->quantity;
                    $inventory->quantity += $quantity;
                    
                    // Update status based on new quantity
                    $this->updateInventoryStatus($inventory, $inventory->quantity);
                    $inventory->save();
                    
                    // Create stock adjustment record
                    StockAdjustment::create([
                        'inventory_id' => $inventory->id,
                        'item_id' => $inventory->item_id,
                        'business_id' => $businessId,
                        'user_id' => $userId,
                        'adjustment_type' => 'increase',
                        'quantity' => $quantity,
                        'previous_quantity' => $currentQuantity,
                        'new_quantity' => $inventory->quantity,
                        'reason_id' => null,
                        'notes' => "Quantity increased from goods receipt #{$goodsReceipt->receipt_number}",
                        'date' => now(),
                        'reference' => 'goods_receipt:' . $goodsReceiptId,
                    ]);
                    
                    // Create stock movement for tracking
                    StockMovement::create([
                        'business_id' => $businessId,
                        'item_id' => $item->id,
                        'to_warehouse_id' => $warehouseId,
                        'to_bin_location_id' => $binLocationId,
                        'quantity' => $quantity,
                        'movement_type' => 'receipt',
                        'reference_type' => 'goods_receipt',
                        'reference_id' => $goodsReceiptId,
                        'notes' => "Receipt of item {$item->name} from goods receipt #{$goodsReceipt->receipt_number}",
                        'approved_by' => $userId,
                        'approved_date' => now(),
                    ]);
                }
                
                $createdInventories[] = $inventory;
                
                // Handle batch tracking if applicable
                if ($item->track_batch && $receiptItem->batch_number) {
                    $batchNumber = $receiptItem->batch_number;
                    $lotNumber = $receiptItem->lot_number;
                    $expiryDate = $receiptItem->expiry_date;
                    $manufacturingDate = $receiptItem->manufacturing_date;
                    
                    // Check if batch already exists
                    $batch = InventoryBatch::where('inventory_id', $inventory->id)
                        ->where('batch_number', $batchNumber)
                        ->where('lot_number', $lotNumber ?? '')
                        ->first();
                    
                    if (!$batch) {
                        // Create new batch
                        $batch = InventoryBatch::create([
                            'inventory_id' => $inventory->id,
                            'batch_number' => $batchNumber,
                            'lot_number' => $lotNumber,
                            'quantity' => $quantity,
                            'manufacturing_date' => $manufacturingDate,
                            'expiry_date' => $expiryDate,
                            'cost_price' => $purchaseOrderItem ? $purchaseOrderItem->unit_price : 0,
                            'supplier_id' => $goodsReceipt->purchaseOrder ? $goodsReceipt->purchaseOrder->supplier_id : null,
                            'purchase_order_id' => $goodsReceipt->purchase_order_id,
                            'received_date' => $goodsReceipt->receipt_date ?? now(),
                            'status' => InventoryBatch::STATUS_AVAILABLE,
                            'barcode' => $receiptItem->barcode,
                            'qr_code' => $receiptItem->qr_code,
                            'rfid_tag' => $receiptItem->rfid_tag,
                        ]);
                    } else {
                        // Update existing batch
                        $batch->quantity += $quantity;
                        $batch->save();
                    }
                    
                    $createdBatches[] = $batch;
                    
                    // Update receipt item status
                    $receiptItem->status = 'stored';
                    $receiptItem->save();
                }
            }
            
            // Update goods receipt status if all items are processed
            $allItemsStored = $goodsReceipt->items()->whereNotIn('status', ['stored', 'rejected'])->count() === 0;
            if ($allItemsStored) {
                $goodsReceipt->status = 'completed';
                $goodsReceipt->save();
            }
            
            DB::commit();
            
            return [
                'status' => true,
                'message' => 'Inventory created successfully from goods receipt',
                'inventories' => $createdInventories,
                'batches' => $createdBatches,
                'goods_receipt' => $goodsReceipt
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory creation from goods receipt failed: ' . $e->getMessage(), [
                'goods_receipt_id' => $goodsReceiptId,
                'options' => $options,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => false,
                'message' => 'Failed to create inventory from goods receipt',
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate a unique batch number
     *
     * @param int $itemId
     * @return string
     */
    private function generateBatchNumber($itemId)
    {
        $prefix = 'BT';
        $itemPart = str_pad($itemId, 5, '0', STR_PAD_LEFT);
        $datePart = date('ymd');
        $randomPart = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $datePart . $itemPart . $randomPart;
    }

    /**
     * Update inventory status based on quantity
     *
     * @param Inventory $inventory - The inventory record to update
     * @param float $newQuantity - The new quantity
     * @return void
     */
    protected function updateInventoryStatus(Inventory $inventory, float $newQuantity): void
    {
        if ($newQuantity <= 0) {
            $inventory->status = Inventory::STATUS_OUT_OF_STOCK;
        } else if ($inventory->reorder_point && $newQuantity <= $inventory->reorder_point) {
            $inventory->status = Inventory::STATUS_LOW_STOCK;
        } else {
            $inventory->status = Inventory::STATUS_IN_STOCK;
        }
    }

    /**
     * Determine the movement type based on reference type
     *
     * @param string $referenceType - The reference type (e.g., 'stock_adjustment', 'sale', 'purchase')
     * @return string - The corresponding movement type
     */
    protected function determineMovementType(string $referenceType): string
    {
        $movementTypeMap = [
            'stock_adjustment' => 'adjustment',
            'sale' => 'sale',
            'purchase' => 'purchase',
            'return' => 'return',
            'transfer_out' => 'transfer',
            'transfer_in' => 'transfer',
            'goods_receipt' => 'receipt',
            'shipment' => 'shipment',
            'stock_count' => 'adjustment',
        ];
        
        return $movementTypeMap[$referenceType] ?? 'other';
    }

    /**
     * Generate a default note for stock movements and adjustments
     *
     * @param string $referenceType - The reference type (e.g., 'stock_adjustment', 'sale', 'purchase')
     * @param string $adjustmentType - The adjustment type ('increase' or 'decrease')
     * @return string - A default note
     */
    protected function generateDefaultNote(string $referenceType, string $adjustmentType): string
    {
        $action = $adjustmentType === 'increase' ? 'added to' : 'removed from';
        
        $noteMap = [
            'stock_adjustment' => "Inventory {$action} stock via manual adjustment",
            'sale' => "Inventory {$action} stock via sale",
            'purchase' => "Inventory {$action} stock via purchase",
            'return' => "Inventory {$action} stock via return",
            'transfer_out' => "Inventory transferred out",
            'transfer_in' => "Inventory transferred in",
            'goods_receipt' => "Inventory received from supplier",
            'shipment' => "Inventory shipped to customer",
            'stock_count' => "Inventory adjusted after stock count",
        ];
        
        return $noteMap[$referenceType] ?? "Inventory {$action} stock";
    }
}
