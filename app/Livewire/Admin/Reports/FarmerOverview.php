<?php

namespace App\Livewire\Admin\Reports;

use App\Models\District;
use App\Models\Region;
use App\Models\User;
use App\Models\ValueChain;
use App\Services\FarmerReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Title('Farmer Overview Report')]
class FarmerOverview extends Component
{
    protected FarmerReportService $reportService;

    #[Url(as: 'region', except: '')]
    public ?int $regionId = null;

    #[Url(as: 'district', except: '')]
    public ?int $districtId = null;

    #[Url(as: 'verification', except: '')]
    public string $verificationStatus = '';

    #[Url(as: 'registration', except: '')]
    public string $registrationSource = '';

    #[Url(as: 'value_chain', except: '')]
    public ?int $valueChainId = null;

    public function boot(FarmerReportService $reportService): void
    {
        $this->reportService = $reportService;
    }

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->can('reports.view') || auth()->user()?->can('reports.view.region'),
            403,
        );

        if (auth()->user()?->isRegionalAdmin()) {
            $this->regionId = auth()->user()->region_id;
        }
    }

    public function updatedRegionId(): void
    {
        $this->districtId = auth()->user()?->district_id && auth()->user()?->region_id === $this->regionId
            ? (int) auth()->user()->district_id
            : null;
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless(auth()->user()?->can('farmers.export'), 403);

        $rows = $this->reportService->exportRows($this->filters(), auth()->user());

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'Full name',
                'Phone',
                'Verification status',
                'Registration source',
                'Region',
                'District',
                'Farm name',
                'Value chains',
            ]);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, 'farmer-overview-'.now()->format('Ymd_His').'.csv');
    }

    public function render(): View
    {
        $user = auth()->user();
        $summary = $this->reportService->summary($this->filters(), $user);

        return view('livewire.admin.reports.farmer-overview', [
            'districts' => $this->districts($user),
            'regions' => $this->regions($user),
            'summary' => $summary,
            'valueChains' => ValueChain::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(): array
    {
        return [
            'district_id' => $this->districtId,
            'region_id' => $this->regionId,
            'registration_source' => $this->registrationSource,
            'value_chain_id' => $this->valueChainId,
            'verification_status' => $this->verificationStatus,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\Region>
     */
    private function regions(User $user): Collection
    {
        return Region::query()
            ->when($user->region_id, fn ($query) => $query->whereKey($user->region_id))
            ->orderBy('name')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \App\Models\District>
     */
    private function districts(User $user): Collection
    {
        return District::query()
            ->when($this->regionId, fn ($query) => $query->where('region_id', $this->regionId))
            ->when($user->district_id, fn ($query) => $query->whereKey($user->district_id))
            ->orderBy('name')
            ->get();
    }
}
