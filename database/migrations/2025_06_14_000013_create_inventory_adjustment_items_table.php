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
        Schema::create('inventory_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_adjustment_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('resource_item')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->onDelete('set null');
            $table->foreignId('bin_location_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity_before', 12, 2);
            $table->decimal('quantity_after', 12, 2);
            $table->decimal('quantity_adjusted', 12, 2);
            $table->string('adjustment_type'); // increase, decrease
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_items');
    }
};
