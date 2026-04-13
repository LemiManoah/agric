<?php

use App\Enums\ListingStatus;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('product_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('linked_supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->longText('description')->nullable();
            $table->foreignId('quality_grade_id')->nullable()->constrained('quality_grades')->nullOnDelete();
            $table->string('unit_of_measure', 60);
            $table->decimal('price_per_unit_usd', 14, 2);
            $table->decimal('minimum_order_quantity', 14, 2)->default(1);
            $table->decimal('stock_available', 14, 2)->default(0);
            $table->string('listing_status', 30)->default(ListingStatus::Draft->value)->index();
            $table->string('warehouse_sku')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_category_id', 'listing_status']);
            $table->index(['linked_supplier_id', 'listing_status']);
            $table->index(['name', 'listing_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
