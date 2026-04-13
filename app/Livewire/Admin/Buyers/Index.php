<?php

namespace App\Livewire\Admin\Buyers;

use App\Models\Buyer;
use App\Services\Exports\CsvExportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Buyers')]
class Index extends Component
{
    use WithPagination;

    protected CsvExportService $csvExportService;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $verificationStatus = '';

    #[Url(as: 'country', except: '')]
    public string $country = '';

    #[Url(as: 'business_type', except: '')]
    public string $businessType = '';

    public function boot(CsvExportService $csvExportService): void
    {
        $this->csvExportService = $csvExportService;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Buyer::class);
    }

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'verificationStatus', 'country', 'businessType'], true)) {
            $this->resetPage();
        }
    }

    public function exportCsv()
    {
        $this->authorize('export', Buyer::class);

        $rows = $this->buyerQuery()
            ->with('valueChainInterests')
            ->orderBy('company_name')
            ->get()
            ->map(fn (Buyer $buyer): array => [
                'company_name' => $buyer->company_name,
                'country' => $buyer->country,
                'business_type' => $buyer->business_type,
                'contact_person_full_name' => $buyer->contact_person_full_name,
                'phone' => $buyer->phone,
                'email' => $buyer->email,
                'verification_status' => $buyer->verification_status->value,
                'value_chain_interests' => $buyer->valueChainInterests->pluck('name')->implode(', '),
            ]);

        return $this->csvExportService->streamDownload('buyers-'.now()->format('Ymd_His').'.csv', $rows);
    }

    public function render(): View
    {
        return view('livewire.admin.buyers.index', [
            'businessTypes' => $this->buyerQuery()->clone()->select('business_type')->distinct()->orderBy('business_type')->pluck('business_type'),
            'buyers' => $this->buyerQuery()
                ->with('valueChainInterests')
                ->orderBy('company_name')
                ->paginate(12),
            'countries' => $this->buyerQuery()->clone()->select('country')->distinct()->orderBy('country')->pluck('country'),
        ])->layout('components.layouts.app');
    }

    private function buyerQuery(): Builder
    {
        return Buyer::query()
            ->visibleTo(auth()->user())
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $searchQuery): void {
                    $searchQuery
                        ->where('company_name', 'like', '%'.$this->search.'%')
                        ->orWhere('contact_person_full_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->verificationStatus !== '', fn (Builder $query) => $query->where('verification_status', $this->verificationStatus))
            ->when($this->country !== '', fn (Builder $query) => $query->where('country', $this->country))
            ->when($this->businessType !== '', fn (Builder $query) => $query->where('business_type', $this->businessType));
    }
}
