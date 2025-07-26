<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PurchaseOrder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('business_id')->on('business');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('po_number')->unique();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->unsignedTinyInteger('status')->default(PurchaseOrder::STATUS_DRAFT); // 0=draft, 1=submitted, 2=approved, 3=sent, 4=partial, 5=received, 6=cancelled, 7=on_hold
            $table->string('payment_status')->default('pending'); // pending, partial, paid, overdue
            $table->string('payment_terms')->nullable();
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency_code')->default('USD');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->decimal('total_quantity_ordered', 12, 2)->default(0);
            $table->decimal('total_quantity_received', 12, 2)->default(0);
            $table->decimal('quantity_rejected', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
