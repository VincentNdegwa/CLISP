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
        Schema::create('business', function (Blueprint $table) {
            $table->id('business_id');
            $table->string('business_name');
            $table->string('business_type_id');
            $table->string('location');
            $table->string('phone_number');
            $table->string('email')->unique();
            $table->string('website')->nullable();
            $table->string('industry_id');
            $table->string('registration_number')->unique();
            $table->string('logo')->nullable();
            $table->date('date_registered');
            $table->decimal('trust_score', 5, 2)->default(0.00);
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->string('subscription_plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business');
    }
};