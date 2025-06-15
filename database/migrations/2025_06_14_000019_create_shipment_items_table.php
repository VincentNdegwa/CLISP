<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ShipmentItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('resource_item')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->onDelete('set null');
            $table->decimal('quantity', 12, 2);
            $table->text('notes')->nullable();
            $table->unsignedTinyInteger('status')->default(ShipmentItem::STATUS_PENDING); // 0=pending, 1=shipped, 2=delivered, 3=returned
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
