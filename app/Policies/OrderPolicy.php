<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return ($user->hasRole('buyer') || $user->hasRole('agent'))
            ? $user->can('orders.view.own')
            : $user->can('orders.view.region') && $user->isRegionalAdmin();
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole('buyer') && $user->can('orders.view.own')) {
            return (int) $order->buyer?->user_id === (int) $user->id;
        }

        if ($user->hasRole('agent') && $user->can('orders.view.own')) {
            return (int) $order->agent?->user_id === (int) $user->id;
        }

        return $user->can('orders.view.region')
            && $user->isRegionalAdmin()
            && $this->withinScope($user, $order);
    }

    public function create(User $user): bool
    {
        return $user->can('orders.create') && ($user->hasRole('buyer') || $user->hasRole('agent'));
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isRegionalAdmin()
            && $this->withinScope($user, $order)
            && (
                $user->can('orders.confirm')
                || $user->can('orders.process')
                || $user->can('orders.dispatch')
                || $user->can('orders.deliver')
                || $user->can('orders.refund')
            );
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->hasRole('buyer') && $user->can('orders.cancel')) {
            return (int) $order->buyer?->user_id === (int) $user->id;
        }

        if ($user->hasRole('agent') && $user->can('orders.cancel')) {
            return (int) $order->agent?->user_id === (int) $user->id;
        }

        return $user->isRegionalAdmin()
            && $user->can('orders.cancel')
            && $this->withinScope($user, $order);
    }

    public function export(User $user): bool
    {
        return $user->isRegionalAdmin()
            && $user->can('orders.view.region')
            && $user->can('exports.create');
    }

    private function withinScope(User $user, Order $order): bool
    {
        return Order::query()
            ->visibleTo($user)
            ->whereKey($order->id)
            ->exists();
    }
}
