<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Shipment;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('business_id')->on('business');
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
            $table->string('shipment_number')->unique();
            $table->date('shipment_date');
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_cost', 12, 2)->nullable();
            $table->unsignedTinyInteger('status')->default(Shipment::STATUS_PENDING); // 0=pending, 1=processing, 2=shipped, 3=delivered, 4=returned
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('packed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('packed_at')->nullable();
            $table->foreignId('shipped_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('shipped_at')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_country')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('package_weight')->nullable();
            $table->string('package_dimensions')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
