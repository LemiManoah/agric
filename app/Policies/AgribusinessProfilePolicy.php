<?php

namespace App\Policies;

use App\Models\AgribusinessProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgribusinessProfilePolicy
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
        return $user->can('agribusiness_profiles.view');
    }

    public function view(User $user, AgribusinessProfile $agribusinessProfile): bool
    {
        return $user->can('agribusiness_profiles.view') && $this->withinScope($user, $agribusinessProfile);
    }

    public function create(User $user): bool
    {
        return $user->can('agribusiness_profiles.create');
    }

    public function update(User $user, AgribusinessProfile $agribusinessProfile): bool
    {
        return $user->can('agribusiness_profiles.update') && $this->withinScope($user, $agribusinessProfile);
    }

    public function export(User $user): bool
    {
        return $user->can('agribusiness_profiles.export');
    }

    private function withinScope(User $user, AgribusinessProfile $agribusinessProfile): bool
    {
        if (! $user->isRegionalAdmin()) {
            return true;
        }

        $agribusinessProfile->loadMissing('districts');

        return $agribusinessProfile->districts->contains(
            fn ($district): bool => (int) $district->region_id === (int) $user->region_id
        );
    }
}
