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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['purchase', 'sale', 'leasing', 'borrowing']);
            $table->enum('status', ['pending', 'completed', 'canceled'])->default('pending');
            $table->unsignedBigInteger('initiator_id');
            $table->unsignedBigInteger('receiver_business_id')->nullable();
            $table->unsignedBigInteger('receiver_customer_id')->nullable();
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
