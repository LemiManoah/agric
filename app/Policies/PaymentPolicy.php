<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
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
        if ($user->hasRole('buyer')) {
            return $user->can('payments.view');
        }

        return $user->hasRole('regional_admin') && $user->can('payments.view');
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->hasRole('buyer')) {
            return $user->can('payments.view')
                && (int) $payment->order?->buyer?->user_id === (int) $user->id;
        }

        return $user->hasRole('regional_admin')
            && $user->can('payments.view')
            && $this->withinScope($user, $payment);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('regional_admin') && $user->can('payments.create');
    }

    public function confirm(User $user, Payment $payment): bool
    {
        return $user->hasRole('regional_admin')
            && $user->can('payments.confirm')
            && $this->withinScope($user, $payment);
    }

    public function refund(User $user, Payment $payment): bool
    {
        return $user->hasRole('regional_admin')
            && $user->can('payments.refund')
            && $this->withinScope($user, $payment);
    }

    public function export(User $user): bool
    {
        return $user->hasRole('regional_admin') && $user->can('payments.export');
    }

    private function withinScope(User $user, Payment $payment): bool
    {
        return Payment::query()
            ->whereKey($payment->id)
            ->visibleTo($user)
            ->exists();
    }
}
