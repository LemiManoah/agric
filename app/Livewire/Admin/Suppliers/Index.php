<?php

namespace App\Livewire\Admin\Suppliers;

use App\Models\District;
use App\Models\Supplier;
use App\Models\ValueChain;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Suppliers')]
class Index extends Component
{
    use WithPagination;

    protected CsvExportService $csvExportService;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'verification', except: '')]
    public string $verificationStatus = '';

    #[Url(as: 'warehouse', except: '')]
    public string $warehouseLinked = '';

    #[Url(as: 'district', except: '')]
    public ?int $districtId = null;

    #[Url(as: 'value_chain', except: '')]
    public ?int $valueChainId = null;

    public function boot(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Supplier::class);
    }

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'verificationStatus', 'warehouseLinked', 'districtId', 'valueChainId'], true)) {
            $this->resetPage();
        }
    }

    public function exportCsv()
    {
        $this->authorize('export', Supplier::class);

        $rows = $this->supplierQuery()
            ->with(['district.region', 'valueChains'])
            ->orderBy('business_name')
            ->get()
            ->map(fn (Supplier $supplier): array => [
                'business_name' => $supplier->business_name,
                'contact_person' => $supplier->contact_person,
                'phone' => $supplier->phone,
                'district' => $supplier->district?->name,
                'verification_status' => $supplier->verification_status->value,
                'warehouse_linked' => $supplier->warehouse_linked ? 'Yes' : 'No',
                'value_chains' => $supplier->valueChains->pluck('name')->implode(', '),
            ]);

        return $this->csvExportService->streamDownload('suppliers-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        $user = auth()->user();

        $suppliers = $this->supplierQuery()
            ->with(['district.region', 'farmer', 'valueChains'])
            ->orderBy('business_name')
            ->paginate(12);

        $districts = District::query()
            ->when($user?->region_id, fn ($query) => $query->where('region_id', $user->region_id))
            ->orderBy('name')
            ->get();

        $valueChains = ValueChain::query()->where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.suppliers.index', [
            'districts' => $districts,
            'suppliers' => $suppliers,
            'valueChains' => $valueChains,
        ])->layout('components.layouts.app');
    }

    private function supplierQuery(): Builder
    {
        return Supplier::query()
            ->visibleTo(auth()->user())
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $searchQuery): void {
                    $searchQuery
                        ->where('business_name', 'like', '%'.$this->search.'%')
                        ->orWhere('contact_person', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->verificationStatus !== '', fn (Builder $query) => $query->where('verification_status', $this->verificationStatus))
            ->when($this->warehouseLinked !== '', fn (Builder $query) => $query->where('warehouse_linked', $this->warehouseLinked === '1'))
            ->when($this->districtId, fn (Builder $query) => $query->where('operating_district_id', $this->districtId))
            ->when($this->valueChainId, function (Builder $query): void {
                $query->whereHas('valueChains', function (Builder $valueChainQuery): void {
                    $valueChainQuery->whereKey($this->valueChainId);
                });
            });
    }
}
