<?php

namespace App\Services;

use App\Enums\ListingStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function assertAvailable(Product $product, float $quantity): void
    {
        if ($product->listing_status !== ListingStatus::Active) {
            throw ValidationException::withMessages([
                'product' => 'Only active listings can be ordered.',
            ]);
        }

        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Stock operations require a positive quantity.',
            ]);
        }

        if ((float) $product->stock_available < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock is available for the selected product.',
            ]);
        }
    }

    public function reserve(Product $product, float $quantity, ?User $actor = null): void
    {
        $product->refresh();
        $this->assertAvailable($product, $quantity);

        $remainingStock = round((float) $product->stock_available - $quantity, 2);

        $product->forceFill([
            'stock_available' => $remainingStock,
            'listing_status' => $remainingStock > 0 ? $product->listing_status : ListingStatus::OutOfStock,
        ])->save();

        $this->log('stock.reserved', $product, $actor, $quantity);
    }

    public function release(Product $product, float $quantity, ?User $actor = null): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Stock operations require a positive quantity.',
            ]);
        }

        $product->refresh();
        $restoredStock = round((float) $product->stock_available + $quantity, 2);

        $product->forceFill([
            'stock_available' => $restoredStock,
            'listing_status' => $restoredStock > 0 && $product->listing_status === ListingStatus::OutOfStock
                ? ListingStatus::Active
                : $product->listing_status,
        ])->save();

        $this->log('stock.released', $product, $actor, $quantity);
    }

    private function log(string $event, Product $product, ?User $actor, float $quantity): void
    {
        activity()
            ->performedOn($product)
            ->causedBy($actor)
            ->event($event)
            ->withProperties([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'stock_available' => $product->stock_available,
            ])
            ->log($event);
    }
}
