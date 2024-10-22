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
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('account_type');
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('paypal_email')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreign('business_id')->references('business_id')->on('business')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};
