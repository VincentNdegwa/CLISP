<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SalesOrder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('business_id')->on('business');
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->date('order_date');
            $table->date('required_date')->nullable();
            $table->date('shipping_date')->nullable();
            $table->unsignedTinyInteger('status')->default(SalesOrder::STATUS_DRAFT); // 0=draft, 1=confirmed, 2=processing, 3=picking, 4=packing, 5=shipped, 6=delivered, 7=cancelled, 8=on_hold
            $table->unsignedTinyInteger('payment_status')->default(SalesOrder::PAYMENT_PENDING); // 0=pending, 1=partial, 2=paid, 3=refunded
            $table->unsignedTinyInteger('fulfillment_status')->default(SalesOrder::FULFILLMENT_PENDING); // 0=pending, 1=partial, 2=fulfilled, 3=backordered
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency_code')->default('USD');
            $table->text('notes')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('source')->nullable(); // web, phone, email, in-store, marketplace
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('total_quantity', 12, 2)->default(0);
            $table->decimal('total_fulfilled', 12, 2)->default(0);
            $table->decimal('total_backordered', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
