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
        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained('resource_item')->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained('inventory_batches')->onDelete('set null');
            $table->foreignId('bin_location_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('expected_quantity', 12, 2);
            $table->decimal('counted_quantity', 12, 2)->nullable();
            $table->decimal('discrepancy', 12, 2)->nullable();
            $table->decimal('discrepancy_percentage', 8, 2)->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('discrepancy_value', 12, 2)->nullable();
            $table->string('status')->default('pending'); // pending, counted, verified, adjusted
            $table->text('notes')->nullable();
            $table->foreignId('counted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('counted_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->boolean('requires_recount')->default(false);
            $table->boolean('adjustment_created')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
    }
};
