<?php

namespace App\Livewire\Admin\Reports;

use App\Enums\AgentOnboardingStatus;
use App\Enums\AgribusinessEntityType;
use App\Enums\VerificationStatus;
use App\Models\Agent;
use App\Models\AgribusinessProfile;
use App\Models\District;
use App\Models\Region;
use App\Models\Supplier;
use App\Models\User;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Title('M1 Profile Summary')]
class M1ProfileSummary extends Component
{
    protected CsvExportService $csvExportService;

    #[Url(as: 'region', except: '')]
    public ?int $regionId = null;

    #[Url(as: 'district', except: '')]
    public ?int $districtId = null;

    public function boot(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
    }

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->can('reports.view') || auth()->user()?->can('reports.view.region'),
            403,
        );

        if (auth()->user()?->isRegionalAdmin()) {
            $this->regionId = auth()->user()?->region_id ? (int) auth()->user()->region_id : null;
        }
    }

    public function updatedRegionId(): void
    {
        $this->districtId = null;
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless(auth()->user()?->can('exports.create'), 403);

        $summary = $this->summary();
        $rows = collect([
            ['metric' => 'total_suppliers', 'value' => $summary['total_suppliers']],
            ['metric' => 'verified_suppliers', 'value' => $summary['verified_suppliers']],
            ['metric' => 'warehouse_linked_suppliers', 'value' => $summary['warehouse_linked_suppliers']],
            ['metric' => 'total_agents', 'value' => $summary['total_agents']],
            ['metric' => 'active_agents', 'value' => $summary['active_agents']],
        ])->merge(
            collect($summary['agribusiness_by_entity'])->map(fn (int $count, string $entity): array => [
                'metric' => 'agribusiness_'.$entity,
                'value' => $count,
            ])
        );

        return $this->csvExportService->streamDownload('m1-profile-summary-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.admin.reports.m1-profile-summary', [
            'districts' => $this->districts($user),
            'regions' => $this->regions($user),
            'summary' => $this->summary(),
        ])->layout('components.layouts.app');
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(): array
    {
        $supplierQuery = $this->supplierQuery();
        $agentQuery = $this->agentQuery();
        $agribusinessQuery = $this->agribusinessQuery();

        return [
            'total_suppliers' => (clone $supplierQuery)->count(),
            'verified_suppliers' => (clone $supplierQuery)->where('verification_status', VerificationStatus::Verified->value)->count(),
            'warehouse_linked_suppliers' => (clone $supplierQuery)->where('warehouse_linked', true)->count(),
            'total_agents' => (clone $agentQuery)->count(),
            'active_agents' => (clone $agentQuery)->where('onboarding_status', AgentOnboardingStatus::Active->value)->count(),
            'agribusiness_by_entity' => collect(AgribusinessEntityType::cases())
                ->mapWithKeys(fn (AgribusinessEntityType $type): array => [
                    $type->value => (clone $agribusinessQuery)->where('entity_type', $type->value)->count(),
                ])
                ->all(),
        ];
    }

    private function supplierQuery(): Builder
    {
        return Supplier::query()
            ->visibleTo(auth()->user())
            ->when($this->regionId, function (Builder $query): void {
                $query->where(function (Builder $scopeQuery): void {
                    $scopeQuery
                        ->whereHas('district', fn (Builder $districtQuery) => $districtQuery->where('region_id', $this->regionId))
                        ->orWhereHas('farmer.location', fn (Builder $locationQuery) => $locationQuery->where('region_id', $this->regionId));
                });
            })
            ->when($this->districtId, function (Builder $query): void {
                $query->where(function (Builder $scopeQuery): void {
                    $scopeQuery
                        ->where('operating_district_id', $this->districtId)
                        ->orWhereHas('farmer.location', fn (Builder $locationQuery) => $locationQuery->where('district_id', $this->districtId));
                });
            });
    }

    private function agentQuery(): Builder
    {
        return Agent::query()
            ->visibleTo(auth()->user())
            ->when($this->regionId, function (Builder $query): void {
                $query->where(function (Builder $scopeQuery): void {
                    $scopeQuery
                        ->whereHas('primaryDistrict', fn (Builder $districtQuery) => $districtQuery->where('region_id', $this->regionId))
                        ->orWhereHas('regions', fn (Builder $regionQuery) => $regionQuery->whereKey($this->regionId));
                });
            })
            ->when($this->districtId, fn (Builder $query) => $query->where('primary_district_id', $this->districtId));
    }

    private function agribusinessQuery(): Builder
    {
        return AgribusinessProfile::query()
            ->visibleTo(auth()->user())
            ->when($this->districtId, fn (Builder $query) => $query->whereHas('districts', fn (Builder $districtQuery) => $districtQuery->whereKey($this->districtId)))
            ->when($this->regionId, fn (Builder $query) => $query->whereHas('districts', fn (Builder $districtQuery) => $districtQuery->where('region_id', $this->regionId)));
    }

    /**
     * @return Collection<int, Region>
     */
    private function regions(User $user): Collection
    {
        return Region::query()
            ->when($user->region_id, fn (Builder $query) => $query->whereKey($user->region_id))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, District>
     */
    private function districts(User $user): Collection
    {
        return District::query()
            ->when($this->regionId, fn (Builder $query) => $query->where('region_id', $this->regionId))
            ->when($user->district_id, fn (Builder $query) => $query->whereKey($user->district_id))
            ->orderBy('name')
            ->get();
    }
}
