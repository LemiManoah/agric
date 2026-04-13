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
        Schema::create('agribusiness_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity_type', 40)->index();
            $table->string('organization_name');
            $table->string('registration_number')->nullable();
            $table->unsignedInteger('membership_size')->nullable();
            $table->unsignedInteger('fleet_size')->nullable();
            $table->text('service_rates')->nullable();
            $table->text('product_range')->nullable();
            $table->decimal('processing_capacity_tonnes_per_day', 12, 2)->nullable();
            $table->text('export_markets')->nullable();
            $table->text('buyer_criteria')->nullable();
            $table->string('contact_person');
            $table->string('contact_phone');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['organization_name', 'entity_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agribusiness_profiles');
    }
};
