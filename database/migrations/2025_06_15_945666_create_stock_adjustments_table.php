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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('inventory_id');
            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->enum('adjustment_type', ['increase', 'decrease']);
            $table->decimal('quantity', 10, 2);
            $table->decimal('previous_quantity', 10, 2);
            $table->decimal('new_quantity', 10, 2);
            $table->unsignedBigInteger('reason_id');
            $table->text('notes')->nullable();
            $table->timestamp('date');
            $table->string('reference')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('item_id')->references('id')->on('resource_item');
            $table->foreign('inventory_id')->references('id')->on('inventories');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('reason_id')->references('id')->on('stock_adjustment_reasons');
            $table->foreign('batch_id')->references('id')->on('inventory_batches');
            
            $table->index('business_id');
            $table->index('adjustment_type');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
