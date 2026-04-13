<?php

namespace App\Services;

use App\Enums\ListingStatus;
use App\Enums\VerificationStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getOrCreateCartForUser(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function addItem(User $user, Product $product, float|int|string $quantity): Cart
    {
        $normalizedQuantity = $this->normalizeQuantity($quantity);

        return DB::transaction(function () use ($normalizedQuantity, $product, $user): Cart {
            $cart = $this->getOrCreateCartForUser($user);
            $existingItem = $cart->items()->where('product_id', $product->id)->first();
            $finalQuantity = $normalizedQuantity + (float) ($existingItem?->quantity ?? 0);

            $this->guardProductAvailability($product->fresh(['supplier']), $finalQuantity);

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $finalQuantity,
                    'unit_price_usd' => $product->price_per_unit_usd,
                ]);
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $finalQuantity,
                    'unit_price_usd' => $product->price_per_unit_usd,
                ]);
            }

            return $cart->fresh(['items.product.images', 'items.product.supplier']);
        });
    }

    public function updateItemQuantity(CartItem $item, float|int|string $quantity, ?User $actor = null): Cart
    {
        $this->guardCartOwnership($item, $actor);

        $normalizedQuantity = $this->normalizeQuantity($quantity, false);

        if ($normalizedQuantity <= 0) {
            return $this->removeItem($item, $actor);
        }

        return DB::transaction(function () use ($item, $normalizedQuantity): Cart {
            $item->loadMissing('product.supplier', 'cart');

            $this->guardProductAvailability($item->product, $normalizedQuantity);

            $item->update([
                'quantity' => $normalizedQuantity,
                'unit_price_usd' => $item->product->price_per_unit_usd,
            ]);

            return $item->cart->fresh(['items.product.images', 'items.product.supplier']);
        });
    }

    public function removeItem(CartItem $item, ?User $actor = null): Cart
    {
        $this->guardCartOwnership($item, $actor);

        return DB::transaction(function () use ($item): Cart {
            $item->loadMissing('cart');
            $cart = $item->cart;
            $item->delete();

            return $cart->fresh(['items.product.images', 'items.product.supplier']);
        });
    }

    public function clearCart(Cart $cart, ?User $actor = null): void
    {
        if ($actor && ! $actor->hasRole('super_admin') && (int) $cart->user_id !== (int) $actor->id) {
            throw new AuthorizationException('You cannot clear another user cart.');
        }

        $cart->items()->delete();
    }

    private function guardCartOwnership(CartItem $item, ?User $actor): void
    {
        if (! $actor) {
            return;
        }

        $item->loadMissing('cart');

        if (! $actor->hasRole('super_admin') && (int) $item->cart->user_id !== (int) $actor->id) {
            throw new AuthorizationException('You cannot manage another user cart.');
        }
    }

    private function guardProductAvailability(Product $product, float $quantity): void
    {
        if ($product->listing_status !== ListingStatus::Active) {
            throw ValidationException::withMessages([
                'product' => 'Only active products can be added to cart.',
            ]);
        }

        if (! $product->supplier || $product->supplier->verification_status !== VerificationStatus::Verified) {
            throw ValidationException::withMessages([
                'product' => 'Only products from verified suppliers can be ordered.',
            ]);
        }

        if ($quantity < (float) $product->minimum_order_quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'The selected quantity is below the minimum order quantity for this product.',
            ]);
        }

        if ((float) $product->stock_available < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'The requested quantity exceeds the available stock for this product.',
            ]);
        }
    }

    private function normalizeQuantity(float|int|string $quantity, bool $rejectZero = true): float
    {
        $normalized = round((float) $quantity, 2);

        if ($rejectZero && $normalized <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be greater than zero.',
            ]);
        }

        return $normalized;
    }
}
