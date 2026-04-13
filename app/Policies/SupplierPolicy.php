<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
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
        return $user->can('suppliers.view');
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->can('suppliers.view') && $this->withinScope($user, $supplier);
    }

    public function create(User $user): bool
    {
        return $user->can('suppliers.create');
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->can('suppliers.update') && $this->withinScope($user, $supplier);
    }

    public function verify(User $user, Supplier $supplier): bool
    {
        return $user->can('suppliers.verify') && $this->withinScope($user, $supplier);
    }

    public function toggleWarehouseLinked(User $user, Supplier $supplier): bool
    {
        return $user->can('suppliers.toggle_warehouse_linked') && $this->withinScope($user, $supplier);
    }

    public function export(User $user): bool
    {
        return $user->can('suppliers.export');
    }

    private function withinScope(User $user, Supplier $supplier): bool
    {
        if (! $user->isRegionalAdmin()) {
            return true;
        }

        $supplier->loadMissing(['district', 'farmer.location']);

        if ($supplier->district?->region_id) {
            return (int) $supplier->district->region_id === (int) $user->region_id;
        }

        return (int) ($supplier->farmer?->location?->region_id ?? 0) === (int) $user->region_id;
    }
}
