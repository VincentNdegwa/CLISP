<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SalesOrderItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('resource_item')->onDelete('cascade');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_percent', 8, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->string('unit_of_measure')->nullable();
            $table->string('description')->nullable();
            $table->unsignedTinyInteger('status')->default(SalesOrderItem::STATUS_PENDING); // 0=pending, 1=allocated, 2=partial, 3=picked, 4=packed, 5=shipped, 6=delivered, 7=cancelled, 8=backordered
            $table->decimal('quantity_allocated', 12, 2)->default(0);
            $table->decimal('quantity_picked', 12, 2)->default(0);
            $table->decimal('quantity_shipped', 12, 2)->default(0);
            $table->decimal('quantity_backordered', 12, 2)->default(0);
            $table->decimal('quantity_returned', 12, 2)->default(0);
            $table->date('expected_shipment_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
