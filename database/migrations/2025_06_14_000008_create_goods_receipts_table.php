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
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('business_id')->on('business');
            $table->string('receipt_number')->unique();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->date('receipt_date');
            $table->string('status')->default('pending'); // pending, inspecting, completed, rejected
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('inspected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('inspected_at')->nullable();
            $table->string('supplier_reference')->nullable();
            $table->string('delivery_note_number')->nullable();
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->decimal('total_quantity', 12, 2)->default(0);
            $table->decimal('total_accepted', 12, 2)->default(0);
            $table->decimal('total_rejected', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
