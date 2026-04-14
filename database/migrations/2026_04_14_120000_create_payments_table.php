<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 50);
            $table->string('gateway_transaction_reference')->nullable()->index();
            $table->json('gateway_reference_payload')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate_to_ugx', 14, 4)->nullable();
            $table->string('status', 50)->index();
            $table->timestamp('paid_at')->nullable()->index();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
