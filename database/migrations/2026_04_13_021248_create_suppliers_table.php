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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('farmer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_name');
            $table->string('contact_person');
            $table->string('phone')->index();
            $table->string('email')->nullable()->index();
            $table->foreignId('operating_district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->decimal('typical_supply_volume_kg_per_month', 14, 2)->nullable();
            $table->string('supply_frequency', 20);
            $table->boolean('warehouse_linked')->default(false)->index();
            $table->string('verification_status')->default('submitted')->index();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_name', 'contact_person']);
            $table->index(['operating_district_id', 'verification_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
