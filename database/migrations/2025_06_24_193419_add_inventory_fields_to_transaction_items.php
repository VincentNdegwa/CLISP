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
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('inventory_id')->nullable()->after('item_id');
            $table->unsignedBigInteger('inventory_batch_id')->nullable()->after('inventory_id');
            
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('set null');
            $table->foreign('inventory_batch_id')->references('id')->on('inventory_batches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);
            $table->dropForeign(['inventory_batch_id']);
            $table->dropColumn(['inventory_id', 'inventory_batch_id']);
        });
    }
};
