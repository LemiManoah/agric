<?php

namespace Database\Seeders;

use App\Enums\ListingStatus;
use App\Enums\OrderStatus;
use App\Enums\VerificationStatus;
use App\Models\Agent;
use App\Models\Buyer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class OrderDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(CartService $cartService, OrderService $orderService): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@agrofresh.test')->first();
        $buyerUser = User::query()->where('email', 'buyer.demo@agrofresh.test')->first();
        $agentUser = User::query()->where('email', 'agent.demo@agrofresh.test')->first();

        $buyer = Buyer::query()->where('user_id', $buyerUser?->id)->first() ?? Buyer::query()->orderBy('id')->first();
        $secondaryBuyer = Buyer::query()
            ->when($buyer, fn ($query) => $query->whereKeyNot($buyer->id))
            ->orderBy('id')
            ->first() ?? $buyer;
        $agent = Agent::query()->where('user_id', $agentUser?->id)->first();

        $products = Product::query()
            ->with('supplier')
            ->where('listing_status', ListingStatus::Active->value)
            ->where('stock_available', '>', 0)
            ->orderBy('id')
            ->take(4)
            ->get();

        $products->each(function (Product $product): void {
            if ($product->supplier && $product->supplier->verification_status !== VerificationStatus::Verified) {
                $product->supplier->update([
                    'verification_status' => VerificationStatus::Verified,
                    'verified_at' => now(),
                ]);
            }
        });

        if (! $buyerUser || ! $buyer || $products->count() < 2) {
            return;
        }

        $this->seedBuyerPendingCart($cartService, $buyerUser, $products->first());

        if (! Order::query()->where('order_number', 'like', 'AGF-'.now()->format('Y').'-%')->exists()) {
            $pendingOrder = $this->seedDirectBuyerOrder($cartService, $orderService, $buyerUser, $buyer, $products->take(2)->all(), [
                'delivery_address' => 'Namanve Industrial Area, Kampala, Uganda',
                'buyer_notes' => 'Seeded pending buyer order for UI review.',
            ]);

            $fulfilledOrder = $this->seedDirectBuyerOrder($cartService, $orderService, $buyerUser, $buyer, [$products->skip(1)->first()], [
                'delivery_address' => 'Kigali Special Economic Zone, Kigali, Rwanda',
                'buyer_notes' => 'Seeded fulfilment path order.',
            ]);

            $orderService->changeStatus($fulfilledOrder, OrderStatus::Confirmed, $superAdmin);
            $orderService->changeStatus($fulfilledOrder, OrderStatus::Processing, $superAdmin);
            $orderService->changeStatus($fulfilledOrder, OrderStatus::Dispatched, $superAdmin);
            $orderService->changeStatus($fulfilledOrder, OrderStatus::Delivered, $superAdmin);

            if ($agentUser && $agent && $secondaryBuyer) {
                $agentOrder = $this->seedAgentOrder($cartService, $orderService, $agentUser, $agent, $secondaryBuyer, [$products->last()], [
                    'delivery_address' => 'Mombasa Road, Nairobi, Kenya',
                    'buyer_notes' => 'Seeded agent-assisted order.',
                ]);

                $orderService->changeStatus($agentOrder, OrderStatus::Confirmed, $superAdmin);
                $orderService->cancelOrder($agentOrder, $superAdmin, 'Seeded cancellation workflow for demos.');
            }

            $pendingOrder->refresh();
        }
    }

    /**
     * @param  array<int, Product>  $products
     * @param  array<string, mixed>  $payload
     */
    private function seedDirectBuyerOrder(CartService $cartService, OrderService $orderService, User $buyerUser, Buyer $buyer, array $products, array $payload): Order
    {
        $cartService->clearCart($cartService->getOrCreateCartForUser($buyerUser), $buyerUser);

        foreach ($products as $product) {
            $cartService->addItem($buyerUser, $product, $product->minimum_order_quantity);
        }

        return $orderService->checkoutBuyer($buyer, $buyerUser, $payload);
    }

    /**
     * @param  array<int, Product>  $products
     * @param  array<string, mixed>  $payload
     */
    private function seedAgentOrder(CartService $cartService, OrderService $orderService, User $agentUser, Agent $agent, Buyer $buyer, array $products, array $payload): Order
    {
        $cartService->clearCart($cartService->getOrCreateCartForUser($agentUser), $agentUser);

        foreach ($products as $product) {
            $cartService->addItem($agentUser, $product, $product->minimum_order_quantity);
        }

        return $orderService->checkoutAgentForBuyer($agent, $buyer, $agentUser, $payload);
    }

    private function seedBuyerPendingCart(CartService $cartService, User $buyerUser, Product $product): void
    {
        $cart = $cartService->getOrCreateCartForUser($buyerUser);
        $cartService->clearCart($cart, $buyerUser);
        $cartService->addItem($buyerUser, $product, $product->minimum_order_quantity);
    }
}
