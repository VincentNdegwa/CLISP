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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payer_name')->nullable();
            $table->string('payer_email')->nullable();
            $table->string('payment_method');
            $table->string("payment_reference")->nullable();
            $table->decimal('paid_amount', 15, 2);
            $table->unsignedBigInteger('transaction_id');
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->unsignedBigInteger('payer_business');
            $table->unsignedBigInteger('payee_business');
            $table->string('currency_code', 3);
            $table->timestamps();

            $table->foreign('transaction_id')
                ->references('id')->on('transactions')
                ->onDelete('cascade');

            $table->foreign('payer_business')
                ->references('business_id')->on('business')
                ->onDelete('cascade');

            $table->foreign('payee_business')
                ->references('business_id')->on('business')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
