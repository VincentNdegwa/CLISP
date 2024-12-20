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
        Schema::create('item_business', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id'); // Foreign key from resource_item.id
            $table->unsignedBigInteger('business_id'); // Foreign key from business.business_id
            $table->decimal('quantity', 10, 2)->default(0); // Quantity of the item
            $table->enum('source', ['Owned', 'Borrowed', 'Leased', 'Purchased'])->default('Owned'); // Source of the item
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('lease_price', 10, 2)->nullable();
            $table->decimal('borrow_fee', 10, 2)->nullable();
            $table->enum('tax_type', ['Inclusive', 'Exclusive'])->default('Exclusive');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('item_id')->references('id')->on('resource_item')->onDelete('cascade');
            $table->foreign('business_id')->references('business_id')->on('business')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_business');
    }
};
