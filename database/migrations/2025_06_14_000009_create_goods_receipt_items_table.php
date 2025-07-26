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
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('item_id')->constrained('resource_item')->onDelete('cascade');
            $table->decimal('quantity_expected', 12, 2)->nullable();
            $table->decimal('quantity_received', 12, 2);
            $table->decimal('quantity_accepted', 12, 2)->default(0);
            $table->decimal('quantity_rejected', 12, 2)->default(0);
            $table->string('rejection_reason')->nullable();
            $table->string('batch_number')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('bin_location_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('pending'); // pending, inspecting, accepted, rejected, partially_accepted
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
    }
};
