<?php

use Carbon\Carbon;
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
        Schema::create('resource_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('category_id')->nullable();
            $table->string('price_currency_code');
            $table->string('unit');
            $table->decimal('price', 10, 2);
            $table->timestamp('date_added')->default(Carbon::now());
            $table->string('item_image')->nullable();
            $table->integer('cloned_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_item');
    }
};
