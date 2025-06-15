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
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->onDelete('cascade');
            $table->string('batch_number')->nullable();
            $table->string('lot_number')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('goods_receipt_item_id')->nullable()->constrained('goods_receipt_items')->onDelete('set null');
            $table->date('received_date')->nullable();
            $table->string('status')->default('available'); // available, reserved, sold, expired, damaged
            $table->string('barcode')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('rfid_tag')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Index for faster lookups
            $table->index(['batch_number', 'lot_number']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_batches');
    }
};
