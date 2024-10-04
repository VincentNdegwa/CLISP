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
            $table->unsignedBigInteger('item_id'); //foreign key from resource_item.id
            $table->unsignedBigInteger('business_id'); // foreign key from business.business_id
            $table->decimal('quantity', 10, 2)->default(0); //quantity of the item
            $table->enum('source', ['Owned', 'Borrowed', 'Leased', 'Purchased'])->default('Owned');
            $table->timestamps();
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
