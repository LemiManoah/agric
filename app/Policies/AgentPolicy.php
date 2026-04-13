<?php

namespace App\Policies;

use App\Models\Agent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AgentPolicy
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
        return $user->can('agents.view');
    }

    public function view(User $user, Agent $agent): bool
    {
        return $user->can('agents.view') && $this->withinScope($user, $agent);
    }

    public function create(User $user): bool
    {
        return $user->can('agents.create');
    }

    public function update(User $user, Agent $agent): bool
    {
        return $user->can('agents.update') && $this->withinScope($user, $agent);
    }

    public function changeStatus(User $user, Agent $agent): bool
    {
        return $user->can('agents.change_status') && $this->withinScope($user, $agent);
    }

    public function export(User $user): bool
    {
        return $user->can('agents.export');
    }

    private function withinScope(User $user, Agent $agent): bool
    {
        if (! $user->isRegionalAdmin()) {
            return true;
        }

        $agent->loadMissing(['primaryDistrict', 'regions']);

        if ((int) $agent->primaryDistrict?->region_id === (int) $user->region_id) {
            return true;
        }

        return $agent->regions->contains('id', $user->region_id);
    }
}
