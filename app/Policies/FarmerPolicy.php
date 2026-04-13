<?php

namespace App\Policies;

use App\Models\Farmer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FarmerPolicy
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
        return $user->can('farmers.view') || $user->can('farmers.view.region');
    }

    public function view(User $user, Farmer $farmer): bool
    {
        if (! ($user->can('farmers.view') || $user->can('farmers.view.region'))) {
            return false;
        }

        return $this->withinScope($user, $farmer);
    }

    public function create(User $user): bool
    {
        return $user->can('farmers.create');
    }

    public function update(User $user, Farmer $farmer): bool
    {
        if (! $user->can('farmers.update')) {
            return false;
        }

        return $this->withinScope($user, $farmer);
    }

    public function verify(User $user, Farmer $farmer): bool
    {
        if (! $user->can('farmers.verify')) {
            return false;
        }

        return $this->withinScope($user, $farmer);
    }

    public function viewMap(User $user): bool
    {
        return $user->can('farmers.view.map');
    }

    private function withinScope(User $user, Farmer $farmer): bool
    {
        if (! ($user->isRegionalAdmin() || $user->hasRole('field_officer'))) {
            return true;
        }

        $farmer->loadMissing('location');

        if (! $farmer->location) {
            return false;
        }

        if ($user->district_id) {
            return (int) $farmer->location->district_id === (int) $user->district_id;
        }

        if ($user->region_id) {
            return (int) $farmer->location->region_id === (int) $user->region_id;
        }

        return false;
    }
}
