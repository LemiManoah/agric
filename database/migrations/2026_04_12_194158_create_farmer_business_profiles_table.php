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
        Schema::create('farmer_business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('farm_name')->nullable();
            $table->string('ursb_registration_number')->nullable();
            $table->decimal('farm_size_acres', 10, 2)->nullable();
            $table->unsignedInteger('number_of_plots')->nullable();
            $table->string('irrigation_availability')->nullable();
            $table->decimal('post_harvest_storage_capacity_tonnes', 10, 2)->nullable();
            $table->boolean('has_warehouse_access')->nullable();
            $table->boolean('cooperative_member')->nullable();
            $table->string('cooperative_name')->nullable();
            $table->string('cooperative_role')->nullable();
            $table->string('average_annual_income_bracket')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farmer_business_profiles');
    }
};
