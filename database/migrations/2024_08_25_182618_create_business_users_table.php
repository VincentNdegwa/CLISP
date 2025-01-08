<?php
// x-xsrf-token

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
        Schema::create('business_users', function (Blueprint $table) {
            $table->id();
            $table->string("business_id");
            $table->string("user_id");
            $table->enum('role', ['Owner', 'Admin', 'Worker']);
            $table->timestamps();
        });

        Schema::create('default_business', function (Blueprint $table) {
            $table->id();
            $table->string("business_id");
            $table->string("user_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_users');
        Schema::dropIfExists('default_business');
    }
};
