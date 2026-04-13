<?php

namespace App\Livewire\Admin\Farmers;

use App\Models\District;
use App\Models\Farmer;
use App\Models\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Farmers')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search', except: '')]
    public string $search = '';

    #[Url(as: 'verification', except: '')]
    public string $verificationStatus = '';

    #[Url(as: 'registration', except: '')]
    public string $registrationSource = '';

    #[Url(as: 'region', except: '')]
    public ?int $regionId = null;

    #[Url(as: 'district', except: '')]
    public ?int $districtId = null;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user?->isRegionalAdmin()) {
            $this->regionId = $user->region_id;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedVerificationStatus(): void
    {
        $this->resetPage();
    }

    public function updatedRegistrationSource(): void
    {
        $this->resetPage();
    }

    public function updatedRegionId(): void
    {
        $this->districtId = null;
        $this->resetPage();
    }

    public function updatedDistrictId(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $user = auth()->user();

        $farmers = Farmer::query()
            ->with(['location.region', 'location.district'])
            ->visibleTo($user)
            ->when($this->search !== '', function (Builder $query): void {
                $query->where(function (Builder $searchQuery): void {
                    $searchQuery
                        ->where('full_name', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->verificationStatus !== '', function (Builder $query): void {
                $query->where('verification_status', $this->verificationStatus);
            })
            ->when($this->registrationSource !== '', function (Builder $query): void {
                $query->where('registration_source', $this->registrationSource);
            })
            ->when($this->regionId, function (Builder $query): void {
                $query->whereHas('location', function (Builder $locationQuery): void {
                    $locationQuery->where('region_id', $this->regionId);
                });
            })
            ->when($this->districtId, function (Builder $query): void {
                $query->whereHas('location', function (Builder $locationQuery): void {
                    $locationQuery->where('district_id', $this->districtId);
                });
            })
            ->orderBy('full_name')
            ->paginate(12);

        $regions = Region::query()
            ->when($user?->isRegionalAdmin(), function (Builder $query) use ($user): void {
                $query->whereKey($user?->region_id);
            })
            ->orderBy('name')
            ->get();

        $districts = District::query()
            ->when($this->regionId, function (Builder $query): void {
                $query->where('region_id', $this->regionId);
            })
            ->orderBy('name')
            ->get();

        return view('livewire.admin.farmers.index', [
            'districts' => $districts,
            'farmers' => $farmers,
            'regions' => $regions,
        ])->layout('components.layouts.app');
    }
}
