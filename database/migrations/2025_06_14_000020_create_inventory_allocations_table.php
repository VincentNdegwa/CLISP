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
        Schema::create('inventory_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('resource_item')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->onDelete('set null');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('bin_location_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity_allocated', 12, 2);
            $table->decimal('quantity_picked', 12, 2)->default(0);
            $table->decimal('quantity_shipped', 12, 2)->default(0);
            $table->string('status')->default('allocated'); // allocated, picked, shipped, cancelled
            $table->foreignId('allocated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('picked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('picked_at')->nullable();
            $table->foreignId('shipment_item_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_allocations');
    }
};
