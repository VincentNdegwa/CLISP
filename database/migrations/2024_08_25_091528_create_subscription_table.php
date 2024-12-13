<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('custom_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['Basic', 'Pro', 'Premium']);
            $table->timestamp('subscription_start')->default(Carbon::now());
            $table->timestamp('subscription_end')->default(Carbon::now()->addMonth());
            $table->enum('payment_status', ['Paid', 'Partial', 'Unpaid'])->default('Unpaid');
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_subscriptions');
    }
};
