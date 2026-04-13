<?php

namespace App\Policies;

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BuyerPolicy
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
        return $user->hasRole('buyer')
            || ($user->can('buyers.view') && $user->hasRole('regional_admin'));
    }

    public function view(User $user, Buyer $buyer): bool
    {
        if ($user->hasRole('buyer')) {
            return (int) $buyer->user_id === (int) $user->id;
        }

        return $user->can('buyers.view')
            && $user->hasRole('regional_admin')
            && $this->withinScope($user, $buyer);
    }

    public function create(User $user): bool
    {
        return $user->can('buyers.create') && $user->hasRole('regional_admin');
    }

    public function update(User $user, Buyer $buyer): bool
    {
        if ($user->hasRole('buyer')) {
            return (int) $buyer->user_id === (int) $user->id;
        }

        return $user->can('buyers.update')
            && $user->hasRole('regional_admin')
            && $this->withinScope($user, $buyer);
    }

    public function verify(User $user, Buyer $buyer): bool
    {
        return $user->can('buyers.verify')
            && $user->hasRole('regional_admin')
            && $this->withinScope($user, $buyer);
    }

    public function export(User $user): bool
    {
        return $user->can('buyers.export') && $user->hasRole('regional_admin');
    }

    private function withinScope(User $user, Buyer $buyer): bool
    {
        if (! $user->isRegionalAdmin()) {
            return true;
        }

        return (int) $buyer->created_by === (int) $user->id;
    }
}
