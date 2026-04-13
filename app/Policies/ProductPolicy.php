<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
        return $user->can('products.view') && $user->hasRole('regional_admin');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('products.view')
            && $user->hasRole('regional_admin')
            && $this->withinScope($user, $product);
    }

    public function create(User $user): bool
    {
        return $user->can('products.create') && $user->hasRole('regional_admin');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.update')
            && $user->hasRole('regional_admin')
            && $this->withinScope($user, $product);
    }

    public function archive(User $user, Product $product): bool
    {
        return $user->can('products.archive')
            && $user->hasRole('regional_admin')
            && $this->withinScope($user, $product);
    }

    public function export(User $user): bool
    {
        return $user->can('products.export') && $user->hasRole('regional_admin');
    }

    private function withinScope(User $user, Product $product): bool
    {
        if (! $user->isRegionalAdmin()) {
            return true;
        }

        $supplierId = $product->linked_supplier_id;

        return Supplier::query()
            ->visibleTo($user)
            ->whereKey($supplierId)
            ->exists();
    }
}
