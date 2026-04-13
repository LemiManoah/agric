<?php

namespace App\Livewire\Admin\AgribusinessProfiles;

use App\Enums\AgribusinessEntityType;
use App\Models\AgribusinessProfile;
use App\Models\District;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Agribusiness Profiles')]
class Index extends Component
{
    use WithPagination;

    protected CsvExportService $csvExportService;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'entity', except: '')]
    public string $entityType = '';

    #[Url(as: 'district', except: '')]
    public ?int $districtId = null;

    public function boot(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', AgribusinessProfile::class);
    }

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'entityType', 'districtId'], true)) {
            $this->resetPage();
        }
    }

    public function exportCsv()
    {
        $this->authorize('export', AgribusinessProfile::class);

        $rows = $this->profileQuery()
            ->with('districts.region')
            ->orderBy('organization_name')
            ->get()
            ->map(fn (AgribusinessProfile $profile): array => [
                'organization_name' => $profile->organization_name,
                'entity_type' => $profile->entity_type->value,
                'contact_person' => $profile->contact_person,
                'contact_phone' => $profile->contact_phone,
                'covered_districts' => $profile->districts->pluck('name')->implode(', '),
            ]);

        return $this->csvExportService->streamDownload('agribusiness-profiles-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        $user = auth()->user();

        return view('livewire.admin.agribusiness-profiles.index', [
            'districts' => District::query()
                ->when($user?->region_id, fn (Builder $query) => $query->where('region_id', $user->region_id))
                ->orderBy('name')
                ->get(),
            'entityTypes' => AgribusinessEntityType::cases(),
            'profiles' => $this->profileQuery()
                ->with('districts.region')
                ->orderBy('organization_name')
                ->paginate(12),
        ])->layout('components.layouts.app');
    }

    private function profileQuery(): Builder
    {
        return AgribusinessProfile::query()
            ->visibleTo(auth()->user())
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $searchQuery): void {
                    $searchQuery
                        ->where('organization_name', 'like', '%'.$this->search.'%')
                        ->orWhere('contact_person', 'like', '%'.$this->search.'%')
                        ->orWhere('contact_phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->entityType !== '', fn (Builder $query) => $query->where('entity_type', $this->entityType))
            ->when($this->districtId, function (Builder $query): void {
                $query->whereHas('districts', function (Builder $districtQuery): void {
                    $districtQuery->whereKey($this->districtId);
                });
            });
    }
}
