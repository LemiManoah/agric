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
        Schema::create('farmer_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->constrained()->restrictOnDelete();
            $table->foreignId('subcounty_id')->constrained()->restrictOnDelete();
            $table->foreignId('parish_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->longText('farm_boundary_geojson')->nullable();
            $table->string('nearest_trading_centre')->nullable();
            $table->decimal('distance_to_tarmac_road_km', 8, 2)->nullable();
            $table->string('internet_access_level')->nullable();
            $table->timestamps();

            $table->index(['region_id', 'district_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmer_locations');
    }
};
