<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
// use App\Models\ResourceItem;
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

            // Store current quantity before adjustment
            $currentQuantity = $inventory->quantity;

            // Calculate new quantity
            if ($adjustmentType === 'increase') {
                $newQuantity = $currentQuantity + $quantity;
            } else {
                // Check if we have enough quantity to decrease
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
                    'business_id' => $businessId,
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
     * Update inventory status based on quantity
     *
     * @param Inventory $inventory
     * @param float $newQuantity
     * @return void
     */
    private function updateInventoryStatus(Inventory $inventory, float $newQuantity)
    {
        if ($newQuantity <= 0) {
            $inventory->status = Inventory::STATUS_OUT_OF_STOCK;
        } elseif ($newQuantity <= $inventory->reorder_point) {
            $inventory->status = Inventory::STATUS_LOW_STOCK;
        } else {
            $inventory->status = Inventory::STATUS_IN_STOCK;
        }
    }

    /**
     * Determine movement type from reference type
     *
     * @param string $referenceType
     * @return string
     */
    private function determineMovementType(string $referenceType)
    {
        $movementTypeMap = [
            'stock_adjustment' => 'adjustment',
            'sale' => 'out',
            'purchase' => 'in',
            'return_in' => 'in',
            'return_out' => 'out',
            'transfer_in' => 'transfer',
            'transfer_out' => 'transfer',
            'damage' => 'out',
            'expired' => 'out',
            'production' => 'in',
            'consumption' => 'out',
            'batch_created' => 'in',
            'batch_adjusted' => 'adjustment',
            'batch_expired' => 'out',
            'batch_damaged' => 'out',
        ];

        return $movementTypeMap[$referenceType] ?? 'adjustment';
    }

    /**
     * Generate default note based on operation
     *
     * @param string $referenceType
     * @param string $adjustmentType
     * @return string
     */
    private function generateDefaultNote(string $referenceType, string $adjustmentType)
    {
        $operation = str_replace('_', ' ', $referenceType);
        return ucfirst($adjustmentType) . " due to " . $operation;
    }
}
