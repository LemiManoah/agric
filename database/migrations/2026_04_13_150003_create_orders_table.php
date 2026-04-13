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
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('buyer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('placed_by_agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->string('status', 40);
            $table->decimal('subtotal', 14, 2);
            $table->decimal('discount_applied', 14, 2)->default(0);
            $table->decimal('order_total', 14, 2);
            $table->string('payment_method', 40)->nullable();
            $table->string('payment_status', 40)->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('delivery_address');
            $table->text('buyer_notes')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['buyer_id', 'status']);
            $table->index(['placed_by_agent_id', 'status']);
            $table->index('ordered_at');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
