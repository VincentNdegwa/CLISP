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
        Schema::create('transactions_details', function (Blueprint $table) {

            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->date('due_date')->nullable();
            $table->date('return_date')->nullable();
            $table->decimal('late_fees', 10, 2)->default(0)->nullable();
            $table->decimal('damage_fees', 10, 2)->default(0)->nullable();
            $table->decimal('shipping_fees', 10, 2)->default(0)->nullable();
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_details');
    }
};
