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
        Schema::create('business_connection', function (Blueprint $table) {
            $table->id();
            $table->string('requesting_business_id');
            $table->string('receiving_business_id');
            $table->enum("connection_status", ["approved", "pending", "rejected"])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_connection');
    }
};
