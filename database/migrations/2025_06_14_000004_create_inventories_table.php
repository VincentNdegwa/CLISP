<?php

use App\Models\Inventory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('resource_item')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('bin_location_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('min_stock_level', 12, 2)->nullable();
            $table->decimal('max_stock_level', 12, 2)->nullable();
            $table->decimal('reorder_point', 12, 2)->nullable();
            $table->unsignedBigInteger('business_id');
            $table->string('status')->default(Inventory::STATUS_IN_STOCK);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('business_id')->references('business_id')->on('business');
            
            $table->unique(
                ['item_id', 'warehouse_id', 'bin_location_id', 'business_id'],
                'inv_item_wh_bin_biz_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
