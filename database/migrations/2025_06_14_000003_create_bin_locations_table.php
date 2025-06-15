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
        Schema::create('bin_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained('warehouse_zones')->onDelete('set null');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('aisle')->nullable();
            $table->string('rack')->nullable();
            $table->string('shelf')->nullable();
            $table->string('bin')->nullable();
            $table->decimal('capacity', 12, 2)->default(0);
            $table->string('capacity_unit')->nullable();
            $table->string('status')->default('active'); // active, inactive, full, maintenance
            $table->string('location_type')->default('standard'); // standard, receiving, shipping, quarantine, returns
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bin_locations');
    }
};
