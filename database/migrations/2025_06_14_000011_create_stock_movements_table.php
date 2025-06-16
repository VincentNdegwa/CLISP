<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('business_id')->on('business');
            $table->foreignId('item_id')->constrained('resource_item')->onDelete('cascade');
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->onDelete('set null');
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->onDelete('set null');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('from_bin_location_id')->nullable()->constrained('bin_locations')->onDelete('set null');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('to_bin_location_id')->nullable()->constrained('bin_locations')->onDelete('set null');
            $table->decimal('quantity', 12, 2);
            $table->string('movement_type'); // receipt, transfer, sale, return, adjustment, stocktake, scrap
            $table->string('reference_type')->nullable(); // PurchaseOrder, SalesOrder, StockCount, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->string('status')->default('completed'); // pending, completed, cancelled
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for faster lookups
            $table->index(['reference_type', 'reference_id']);
            $table->index('movement_type');
            $table->index(['item_id', 'inventory_id', 'batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
