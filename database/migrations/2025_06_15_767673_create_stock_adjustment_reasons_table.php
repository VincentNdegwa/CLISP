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
        Schema::create('stock_adjustment_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('business_id')->default(0); // 0 for default/system reasons
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['increase', 'decrease', 'both'])->default('both');
            $table->timestamps();
            $table->softDeletes();
            
            // Index for faster lookups
            $table->index('business_id');
            $table->index('is_active');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_reasons');
    }
};
