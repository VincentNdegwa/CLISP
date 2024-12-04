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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained('business', 'business_id')->onDelete('set null');
            $table->string('name');
            $table->enum("default", ['false', 'true'])->default('false');
            $table->enum('category', ['Simple', 'Information-Required'])->default('Simple');
            $table->string('icon')->default('pi pi-wallet');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
