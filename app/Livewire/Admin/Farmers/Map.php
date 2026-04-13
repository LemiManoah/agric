<?php

namespace App\Livewire\Admin\Farmers;

use App\Models\District;
use App\Models\Farmer;
use App\Models\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Farm Map')]
class Map extends Component
{
    use AuthorizesRequests;

    #[Url(as: 'region', except: '')]
    public ?int $regionId = null;

    #[Url(as: 'district', except: '')]
    public ?int $districtId = null;

    #[Url(as: 'verification', except: '')]
    public string $verificationStatus = '';

    #[Url(as: 'registration', except: '')]
    public string $registrationSource = '';

    #[Url(as: 'value_chain', except: '')]
    public string $valueChain = '';

    public array $mapPoints = [];

    public int $visibleFarmers = 0;

    public function mount(): void
    {
        $this->authorize('viewMap', Farmer::class);

        $user = auth()->user();

        if ($user?->isRegionalAdmin()) {
            $this->regionId = $user->region_id;
        }

        $this->refreshMapData();
    }

    public function updatedRegionId(): void
    {
        $this->districtId = null;
        $this->refreshMapData();
    }

    public function updatedDistrictId(): void
    {
        $this->refreshMapData();
    }

    public function updatedVerificationStatus(): void
    {
        $this->refreshMapData();
    }

    public function updatedRegistrationSource(): void
    {
        $this->refreshMapData();
    }

    public function updatedValueChain(): void
    {
        $this->refreshMapData();
    }

    public function render(): View
    {
        $user = auth()->user();

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

        return view('livewire.admin.farmers.map', [
            'districts' => $districts,
            'regions' => $regions,
        ])->layout('components.layouts.app');
    }

    private function refreshMapData(): void
    {
        $user = auth()->user();

        $farmers = Farmer::query()
            ->with(['location.region', 'location.district'])
            ->visibleTo($user)
            ->whereHas('location', function (Builder $locationQuery): void {
                $locationQuery
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude');
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
            ->get();

        $this->mapPoints = $farmers
            ->map(function (Farmer $farmer): array {
                return [
                    'id' => $farmer->id,
                    'name' => $farmer->full_name,
                    'phone' => $farmer->phone,
                    'registration_source' => $farmer->registration_source->value,
                    'verification_status' => $farmer->verification_status->value,
                    'region_name' => $farmer->location?->region?->name,
                    'district_name' => $farmer->location?->district?->name,
                    'latitude' => (float) $farmer->location->latitude,
                    'longitude' => (float) $farmer->location->longitude,
                    'farm_boundary_geojson' => $farmer->location->farm_boundary_geojson
                        ? json_decode($farmer->location->farm_boundary_geojson, true)
                        : null,
                ];
            })
            ->values()
            ->all();

        $this->visibleFarmers = count($this->mapPoints);

        $this->dispatch('farmers-map-updated', markers: $this->mapPoints);
    }
}
