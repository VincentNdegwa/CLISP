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
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->foreign('business_id')->references('business_id')->on('business');
            $table->string('count_number')->unique();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained('warehouse_zones')->onDelete('set null');
            $table->date('count_date');
            $table->string('count_type'); // full, cycle, spot, zone
            $table->string('status')->default('draft'); // draft, in_progress, completed, cancelled
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->decimal('total_items_counted', 12, 2)->default(0);
            $table->decimal('total_discrepancies', 12, 2)->default(0);
            $table->decimal('total_discrepancy_value', 12, 2)->default(0);
            $table->decimal('accuracy_percentage', 5, 2)->default(0);
            $table->boolean('adjustments_created')->default(false);
            $table->foreignId('adjustment_id')->nullable()->constrained('inventory_adjustments')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_counts');
    }
};
