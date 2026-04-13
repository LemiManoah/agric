<?php

namespace App\Livewire\Admin\Agents;

use App\Enums\AgentOnboardingStatus;
use App\Models\Agent;
use App\Models\District;
use App\Models\Region;
use App\Models\ValueChain;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Agents')]
class Index extends Component
{
    use WithPagination;

    protected CsvExportService $csvExportService;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $onboardingStatus = '';

    #[Url(as: 'district', except: '')]
    public ?int $primaryDistrictId = null;

    #[Url(as: 'region', except: '')]
    public ?int $regionId = null;

    #[Url(as: 'value_chain', except: '')]
    public ?int $valueChainId = null;

    public function boot(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Agent::class);

        if (auth()->user()?->isRegionalAdmin()) {
            $this->regionId = auth()->user()?->region_id ? (int) auth()->user()->region_id : null;
        }
    }

    public function updatedRegionId(): void
    {
        $this->primaryDistrictId = null;
        $this->resetPage();
    }

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'onboardingStatus', 'primaryDistrictId', 'regionId', 'valueChainId'], true)) {
            $this->resetPage();
        }
    }

    public function exportCsv()
    {
        $this->authorize('export', Agent::class);

        $rows = $this->agentQuery()
            ->with(['primaryDistrict.region', 'regions', 'valueChains'])
            ->orderBy('full_name')
            ->get()
            ->map(fn (Agent $agent): array => [
                'agent_code' => $agent->agent_code,
                'full_name' => $agent->full_name,
                'phone' => $agent->phone,
                'primary_district' => $agent->primaryDistrict?->name,
                'service_regions' => $agent->regions->pluck('name')->implode(', '),
                'onboarding_status' => $agent->onboarding_status->value,
                'commission_rate' => $agent->commission_rate,
                'total_orders_placed' => $agent->total_orders_placed,
                'total_commission_earned' => $agent->total_commission_earned,
                'value_chains' => $agent->valueChains->pluck('name')->implode(', '),
            ]);

        return $this->csvExportService->streamDownload('agents-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.admin.agents.index', [
            'agents' => $this->agentQuery()
                ->with(['primaryDistrict.region', 'regions', 'valueChains'])
                ->orderBy('full_name')
                ->paginate(12),
            'districts' => District::query()
                ->when($this->regionId, fn (Builder $query) => $query->where('region_id', $this->regionId))
                ->when($user?->district_id, fn (Builder $query) => $query->whereKey($user->district_id))
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

    private function agentQuery(): Builder
    {
        return Agent::query()
            ->visibleTo(auth()->user())
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $searchQuery): void {
                    $searchQuery
                        ->where('full_name', 'like', '%'.$this->search.'%')
                        ->orWhere('agent_code', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->onboardingStatus !== '', fn (Builder $query) => $query->where('onboarding_status', $this->onboardingStatus))
            ->when($this->primaryDistrictId, fn (Builder $query) => $query->where('primary_district_id', $this->primaryDistrictId))
            ->when($this->regionId, function (Builder $query): void {
                $query->where(function (Builder $regionQuery): void {
                    $regionQuery
                        ->whereHas('primaryDistrict', fn (Builder $districtQuery) => $districtQuery->where('region_id', $this->regionId))
                        ->orWhereHas('regions', fn (Builder $serviceRegionQuery) => $serviceRegionQuery->whereKey($this->regionId));
                });
            })
            ->when($this->valueChainId, function (Builder $query): void {
                $query->whereHas('valueChains', function (Builder $valueChainQuery): void {
                    $valueChainQuery->whereKey($this->valueChainId);
                });
            });
    }
}
