<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryBatch extends Model
{
    use HasFactory, SoftDeletes;

    // Define status constants for inventory batches
    const STATUS_AVAILABLE = 'available';
    const STATUS_RESERVED = 'reserved';
    const STATUS_SOLD = 'sold';
    const STATUS_EXPIRED = 'expired';
    const STATUS_DAMAGED = 'damaged';

    protected $fillable = [
        'inventory_id',
        'batch_number',
        'lot_number',
        'quantity',
        'manufacturing_date',
        'expiry_date',
        'cost_price',
        'supplier_id',
        'purchase_order_id',
        'received_date',
        'status', // available, reserved, sold, expired, damaged
        'barcode',
        'qr_code',
        'rfid_tag',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function reservations()
    {
        return $this->hasMany(BatchReservation::class);
    }

    // New relationships for transactions
    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'inventory_batch_id');
    }

    // Helper methods for FIFO/LIFO/FEFO
    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return now()->greaterThan($this->expiry_date);
    }

    public function daysUntilExpiry()
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        return now()->diffInDays($this->expiry_date, false);
    }
    
    // Methods for handling sales and inventory management using InventoryManager
    public function reserve($quantity, $transactionId = null)
    {
        if ($this->quantity < $quantity) {
            throw new \Exception("Not enough quantity available in this batch.");
        }
        
        if ($this->status !== self::STATUS_AVAILABLE) {
            throw new \Exception("This batch is not available for reservation.");
        }
        
        // Use InventoryManager for proper tracking
        $inventoryManager = app(\App\Services\InventoryManager::class);
        $result = $inventoryManager->adjustQuantity([
            'inventory_id' => $this->inventory_id,
            'batch_id' => $this->id,
            'adjustment_type' => 'decrease',
            'quantity' => $quantity,
            'reference_type' => 'transaction_reservation',
            'reference_id' => $transactionId,
            'notes' => 'Inventory batch reserved for transaction'
        ]);
        
        if (!$result['status']) {
            throw new \Exception($result['message'] ?? 'Failed to reserve batch');
        }
        
        // Update the batch status if needed
        $this->refresh();
        if ($this->quantity == 0) {
            $this->status = self::STATUS_RESERVED;
            $this->save();
        }
        
        if ($transactionId) {
            // Create reservation record if needed
            BatchReservation::create([
                'inventory_batch_id' => $this->id,
                'transaction_id' => $transactionId,
                'quantity' => $quantity,
                'reserved_at' => now(),
                'status' => 'active'
            ]);
        }
        
        return true;
    }
    
    public function sell($quantity, $transactionId = null)
    {
        if ($this->quantity < $quantity) {
            throw new \Exception("Not enough quantity available in this batch.");
        }
        
        if (!in_array($this->status, [self::STATUS_AVAILABLE, self::STATUS_RESERVED])) {
            throw new \Exception("This batch is not available for selling.");
        }
        
        // Use InventoryManager for proper tracking
        $inventoryManager = app(\App\Services\InventoryManager::class);
        $result = $inventoryManager->adjustQuantity([
            'inventory_id' => $this->inventory_id,
            'batch_id' => $this->id,
            'adjustment_type' => 'decrease',
            'quantity' => $quantity,
            'reference_type' => 'sale',
            'reference_id' => $transactionId,
            'notes' => 'Inventory sold through transaction'
        ]);
        
        if (!$result['status']) {
            throw new \Exception($result['message'] ?? 'Failed to sell batch');
        }
        
        // Update the batch status if needed
        $this->refresh();
        if ($this->quantity == 0) {
            $this->status = self::STATUS_SOLD;
            $this->save();
        }
        
        return true;
    }
    
    public function return($quantity, $transactionId = null)
    {
        // Use InventoryManager for proper tracking of returns
        $inventoryManager = app(\App\Services\InventoryManager::class);
        $result = $inventoryManager->adjustQuantity([
            'inventory_id' => $this->inventory_id,
            'batch_id' => $this->id,
            'adjustment_type' => 'increase',
            'quantity' => $quantity,
            'reference_type' => 'return_in',
            'reference_id' => $transactionId,
            'notes' => 'Inventory returned from transaction'
        ]);
        
        if (!$result['status']) {
            throw new \Exception($result['message'] ?? 'Failed to return inventory');
        }
        
        // Update the batch status if it was previously sold out
        $this->refresh();
        if ($this->status === self::STATUS_SOLD || $this->status === self::STATUS_RESERVED) {
            $this->status = self::STATUS_AVAILABLE;
            $this->save();
        }
        
        return true;
    }
    
    public function getAvailableQuantity()
    {
        if (in_array($this->status, [self::STATUS_SOLD, self::STATUS_EXPIRED, self::STATUS_DAMAGED])) {
            return 0;
        }
        
        return $this->quantity;
    }
}
