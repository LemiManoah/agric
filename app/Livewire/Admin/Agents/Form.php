<?php

namespace App\Livewire\Admin\Agents;

use App\Enums\AgentOnboardingStatus;
use App\Models\Agent;
use App\Models\District;
use App\Models\Region;
use App\Models\ValueChain;
use App\Services\AgentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Agent Form')]
class Form extends Component
{
    protected AgentService $agentService;

    public ?Agent $agent = null;

    public string $full_name = '';

    public string $phone = '';

    public string $email = '';

    public ?int $primary_district_id = null;

    public string $commission_rate = '0.00';

    public string $onboarding_status = '';

    public array $region_ids = [];

    public array $value_chain_ids = [];

    public function boot(AgentService $agentService): void
    {
        $this->agentService = $agentService;
    }

    public function mount(?Agent $agent = null): void
    {
        $this->agent = $agent?->exists ? $agent->load(['primaryDistrict.region', 'regions', 'valueChains']) : null;

        if ($this->agent) {
            $this->authorize('update', $this->agent);

            $this->full_name = $this->agent->full_name;
            $this->phone = $this->agent->phone;
            $this->email = $this->agent->email ?? '';
            $this->primary_district_id = $this->agent->primary_district_id;
            $this->commission_rate = (string) $this->agent->commission_rate;
            $this->onboarding_status = $this->agent->onboarding_status->value;
            $this->region_ids = $this->agent->regions->pluck('id')->map(fn ($id) => (int) $id)->all();
            $this->value_chain_ids = $this->agent->valueChains->pluck('id')->map(fn ($id) => (int) $id)->all();
        } else {
            $this->authorize('create', Agent::class);
            $this->onboarding_status = AgentOnboardingStatus::Onboarding->value;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'primary_district_id' => ['required', 'exists:districts,id'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'onboarding_status' => ['required', Rule::in(array_column(AgentOnboardingStatus::cases(), 'value'))],
            'region_ids' => ['array'],
            'region_ids.*' => ['exists:regions,id'],
            'value_chain_ids' => ['array'],
            'value_chain_ids.*' => ['exists:value_chains,id'],
        ]);

        $agent = $this->agent
            ? $this->agentService->updateAgent($this->agent, $validated, auth()->user())
            : $this->agentService->createAgent($validated, auth()->user());

        session()->flash('status', 'Agent profile saved successfully.');

        return redirect()->route('admin.agents.index', ['highlight' => $agent->id]);
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.admin.agents.form', [
            'agent' => $this->agent,
            'districts' => District::query()
                ->when($user?->region_id, fn (Builder $query) => $query->where('region_id', $user->region_id))
                ->orderBy('name')
                ->get(),
            'regions' => Region::query()
                ->when($user?->region_id, fn (Builder $query) => $query->whereKey($user->region_id))
                ->orderBy('name')
                ->get(),
            'statuses' => AgentOnboardingStatus::cases(),
            'valueChains' => ValueChain::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
