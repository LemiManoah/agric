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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('agent_code')->unique();
            $table->string('phone')->index();
            $table->string('email')->nullable()->index();
            $table->foreignId('primary_district_id')->constrained('districts')->restrictOnDelete();
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->unsignedInteger('total_orders_placed')->default(0);
            $table->decimal('total_commission_earned', 14, 2)->default(0);
            $table->string('onboarding_status', 20)->default('onboarding')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['full_name', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
