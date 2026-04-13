<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Agent;
use App\Models\Buyer;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private CartService $cartService,
        private OrderNumberGenerator $orderNumberGenerator,
        private StockService $stockService,
    ) {}

    public function checkoutBuyer(Buyer $buyer, User $actor, array $payload = []): Order
    {
        throw_unless($actor->can('create', Order::class), AuthorizationException::class);

        if ($actor->hasRole('buyer') && (int) $buyer->user_id !== (int) $actor->id) {
            throw new AuthorizationException('You can only checkout orders for your own buyer account.');
        }

        $cart = $this->cartService->getOrCreateCartForUser($actor);

        return $this->createOrderFromCart($cart, $buyer, $actor, null, $payload);
    }

    public function checkoutAgentForBuyer(Agent $agent, Buyer $buyer, User $actor, array $payload = []): Order
    {
        throw_unless($actor->can('create', Order::class), AuthorizationException::class);

        if ((int) $agent->user_id !== (int) $actor->id) {
            throw new AuthorizationException('You can only place orders for buyers using your own agent profile.');
        }

        $cart = $this->cartService->getOrCreateCartForUser($actor);

        return $this->createOrderFromCart($cart, $buyer, $actor, $agent, $payload);
    }

    public function changeStatus(Order $order, OrderStatus|string $status, ?User $actor = null, ?string $notes = null): Order
    {
        $targetStatus = $status instanceof OrderStatus ? $status : OrderStatus::from($status);

        if ($targetStatus === OrderStatus::Cancelled) {
            return $this->cancelOrder($order, $actor, $notes);
        }

        if ($actor) {
            throw_unless($actor->can('updateStatus', $order), AuthorizationException::class);
            $this->guardTransitionPermission($actor, $targetStatus);
        }

        $order->loadMissing('items.product');

        if ($order->status === $targetStatus) {
            return $order->fresh($this->relations());
        }

        $this->guardTransition($order->status, $targetStatus);

        return DB::transaction(function () use ($actor, $notes, $order, $targetStatus): Order {
            $oldStatus = $order->status;

            $order->forceFill([
                'status' => $targetStatus,
                'confirmed_at' => $targetStatus === OrderStatus::Confirmed ? now() : $order->confirmed_at,
                'dispatched_at' => $targetStatus === OrderStatus::Dispatched ? now() : $order->dispatched_at,
                'delivered_at' => $targetStatus === OrderStatus::Delivered ? now() : $order->delivered_at,
                'cancelled_at' => $targetStatus === OrderStatus::Cancelled ? now() : $order->cancelled_at,
            ])->save();

            $this->writeStatusHistory($order, $oldStatus, $targetStatus, $actor, $notes);
            $this->logEvent('order.status_changed', $order, $actor, ['from' => $oldStatus->value, 'to' => $targetStatus->value, 'notes' => $notes]);

            return $order->fresh($this->relations());
        });
    }

    public function cancelOrder(Order $order, ?User $actor = null, ?string $notes = null): Order
    {
        if ($actor) {
            throw_unless($actor->can('cancel', $order), AuthorizationException::class);
        }

        $order->loadMissing('items.product');

        if ($order->status === OrderStatus::Cancelled) {
            return $order->fresh($this->relations());
        }

        $this->guardTransition($order->status, OrderStatus::Cancelled);

        return DB::transaction(function () use ($actor, $notes, $order): Order {
            foreach ($order->items as $item) {
                if ($item->product) {
                    $this->stockService->release($item->product, (float) $item->quantity, $actor);
                }
            }

            $oldStatus = $order->status;

            $order->forceFill([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
            ])->save();

            $this->writeStatusHistory($order, $oldStatus, OrderStatus::Cancelled, $actor, $notes);
            $this->logEvent('order.cancelled', $order, $actor, ['from' => $oldStatus->value, 'to' => OrderStatus::Cancelled->value, 'notes' => $notes]);

            return $order->fresh($this->relations());
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function createOrderFromCart(Cart $cart, Buyer $buyer, User $actor, ?Agent $agent, array $payload): Order
    {
        $normalized = $this->normalizePayload($payload);

        return DB::transaction(function () use ($actor, $agent, $buyer, $cart, $normalized): Order {
            $cart->loadMissing('items.product.supplier');

            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Add at least one product to the cart before checkout.',
                ]);
            }

            if ($normalized['delivery_address'] === '') {
                throw ValidationException::withMessages([
                    'delivery_address' => 'A delivery address is required before checkout.',
                ]);
            }

            $order = Order::query()->create([
                'order_number' => $this->orderNumberGenerator->generate(),
                'buyer_id' => $buyer->id,
                'placed_by_agent_id' => $agent?->id,
                'status' => OrderStatus::Pending,
                'subtotal' => 0,
                'discount_applied' => 0,
                'order_total' => 0,
                'payment_method' => $normalized['payment_method'],
                'payment_status' => PaymentStatus::Unpaid,
                'payment_reference' => $normalized['payment_reference'],
                'delivery_address' => $normalized['delivery_address'],
                'buyer_notes' => $normalized['buyer_notes'],
                'ordered_at' => now(),
                'created_by' => $actor->id,
            ]);

            $subtotal = 0.0;

            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product?->fresh(['supplier']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'cart' => 'One of the products in the cart is no longer available.',
                    ]);
                }

                $this->stockService->reserve($product, (float) $cartItem->quantity, $actor);

                $lineTotal = round((float) $cartItem->quantity * (float) $cartItem->unit_price_usd, 2);
                $subtotal += $lineTotal;

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'supplier_id' => $product->linked_supplier_id,
                    'product_name_snapshot' => $product->name,
                    'quantity' => $cartItem->quantity,
                    'unit_price_usd' => $cartItem->unit_price_usd,
                    'line_total_usd' => $lineTotal,
                ]);
            }

            $order->update([
                'subtotal' => $subtotal,
                'order_total' => max(0, $subtotal - (float) $order->discount_applied),
            ]);

            $this->writeStatusHistory($order, null, OrderStatus::Pending, $actor, 'Order created at checkout.');
            $this->cartService->clearCart($cart, $actor);

            if ($agent) {
                $agent->increment('total_orders_placed');
            }

            $this->logEvent('order.created', $order, $actor, [
                'buyer_id' => $buyer->id,
                'placed_by_agent_id' => $agent?->id,
                'subtotal' => $subtotal,
            ]);

            return $order->fresh($this->relations());
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{delivery_address: string, buyer_notes: ?string, payment_method: ?PaymentMethod, payment_reference: ?string}
     */
    private function normalizePayload(array $payload): array
    {
        $paymentMethod = $payload['payment_method'] ?? null;

        return [
            'delivery_address' => trim((string) ($payload['delivery_address'] ?? '')),
            'buyer_notes' => $this->nullableString($payload['buyer_notes'] ?? null),
            'payment_method' => $paymentMethod
                ? ($paymentMethod instanceof PaymentMethod ? $paymentMethod : PaymentMethod::from((string) $paymentMethod))
                : null,
            'payment_reference' => $this->nullableString($payload['payment_reference'] ?? null),
        ];
    }

    private function guardTransition(OrderStatus $currentStatus, OrderStatus $targetStatus): void
    {
        $allowedTransitions = [
            OrderStatus::Pending->value => [OrderStatus::Confirmed, OrderStatus::Cancelled],
            OrderStatus::Confirmed->value => [OrderStatus::Processing, OrderStatus::Cancelled],
            OrderStatus::Processing->value => [OrderStatus::Dispatched, OrderStatus::Cancelled],
            OrderStatus::Dispatched->value => [OrderStatus::Delivered],
            OrderStatus::Delivered->value => [OrderStatus::Refunded],
            OrderStatus::Cancelled->value => [],
            OrderStatus::Refunded->value => [],
        ];

        $allowedTargets = collect($allowedTransitions[$currentStatus->value] ?? [])
            ->map(fn (OrderStatus $allowedStatus): string => $allowedStatus->value)
            ->all();

        if (! in_array($targetStatus->value, $allowedTargets, true)) {
            throw ValidationException::withMessages([
                'status' => sprintf('Orders cannot move from %s to %s.', $currentStatus->value, $targetStatus->value),
            ]);
        }
    }

    private function guardTransitionPermission(User $actor, OrderStatus $targetStatus): void
    {
        $permissionMap = [
            OrderStatus::Confirmed->value => 'orders.confirm',
            OrderStatus::Processing->value => 'orders.process',
            OrderStatus::Dispatched->value => 'orders.dispatch',
            OrderStatus::Delivered->value => 'orders.deliver',
            OrderStatus::Refunded->value => 'orders.refund',
        ];

        $permission = $permissionMap[$targetStatus->value] ?? null;

        if ($permission && ! $actor->can($permission)) {
            throw new AuthorizationException('You do not have permission to apply that order status change.');
        }
    }

    private function writeStatusHistory(Order $order, ?OrderStatus $oldStatus, OrderStatus $newStatus, ?User $actor, ?string $notes = null): void
    {
        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by_user_id' => $actor?->id,
            'notes' => $notes,
        ]);
    }

    /**
     * @param  array<string, mixed>  $properties
     */
    private function logEvent(string $event, Order $order, ?User $actor, array $properties = []): void
    {
        activity()
            ->performedOn($order)
            ->causedBy($actor)
            ->event($event)
            ->withProperties([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status->value,
                'properties' => Arr::whereNotNull($properties),
            ])
            ->log($event);
    }

    /**
     * @return array<int, string>
     */
    private function relations(): array
    {
        return [
            'agent.user',
            'buyer.user',
            'creator',
            'items.product.images',
            'items.supplier',
            'statusHistories.changedBy',
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
