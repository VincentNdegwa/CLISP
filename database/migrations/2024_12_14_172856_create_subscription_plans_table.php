<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('product_id');
            $table->string('price_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_cycle', ['monthly', 'annually']);
            $table->json('features')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'billing_cycle']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
};
