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
        Schema::create('farmer_value_chains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('value_chain_id')->constrained()->restrictOnDelete();
            $table->string('production_scale')->nullable();
            $table->decimal('estimated_seasonal_harvest_kg', 12, 2)->nullable();
            $table->string('current_market_destination')->nullable();
            $table->json('input_access_details')->nullable();
            $table->timestamps();

            $table->unique(['farmer_id', 'value_chain_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmer_value_chains');
    }
};
